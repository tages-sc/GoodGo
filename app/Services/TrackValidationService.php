<?php

namespace App\Services;

use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Models\BusStop;
use App\Models\Competition;
use App\Models\Municipality;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\TrainStation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackValidationService
{
    /**
     * Modalità dry run (non salva nel database)
     */
    protected bool $dryRun = false;

    /**
     * Risultato della validazione
     */
    protected array $result = [
        'is_valid' => true,
        'rejection_reason' => null,
        'warnings' => [],
        'segments_validated' => 0,
        'segments_invalid' => 0,
        'total_distance_meters' => 0,
        'valid_distance_meters' => 0,
        'credits_earned' => 0,
        'co2_saved_grams' => 0,
        'calories_burned' => 0,
    ];

    /**
     * Raggio di ricerca stazioni treno in metri
     */
    protected const TRAIN_STATION_RADIUS = 100;

    /**
     * Raggio di ricerca fermate bus in metri
     */
    protected const BUS_STOP_RADIUS = 10;

    /**
     * Province per cui sono disponibili le fermate bus (Pisa e Livorno)
     */
    protected const BUS_PROVINCES = ['PI', 'LI'];

    public function __construct(
        protected TrackParserService $parserService,
        protected EmissionsCalculator $emissionsCalculator,
        protected CreditService $creditService,
        protected BadgeService $badgeService
    ) {}

    /**
     * Esegue la validazione in modalità test (dry run)
     * Non salva nulla nel database, restituisce solo il report dettagliato
     */
    public function dryRun(Track $track): array
    {
        return $this->performValidation($track, dryRun: true);
    }

    /**
     * Esegue la validazione completa di una traccia
     */
    public function validate(Track $track): array
    {
        return $this->performValidation($track, dryRun: false);
    }

    /**
     * Esegue la validazione (comune a validate e dryRun)
     */
    protected function performValidation(Track $track, bool $dryRun = false): array
    {
        $this->dryRun = $dryRun;

        $this->result = [
            'is_valid' => true,
            'dry_run' => $dryRun,
            'rejection_reason' => null,
            'warnings' => [],
            'segments_validated' => 0,
            'segments_invalid' => 0,
            'total_distance_meters' => 0,
            'valid_distance_meters' => 0,
            'credits_earned' => 0,
            'co2_saved_grams' => 0,
            'calories_burned' => 0,
            'segments_details' => [], // Dettagli per ogni segmento
            'competition_checks' => [], // Controlli sulla gara
            'track_info' => [ // Info traccia per report
                'id' => $track->id,
                'session_id' => $track->session_id,
                'user' => $track->user?->name ?? 'N/A',
                'competition' => $track->competition?->name ?? 'Nessuna',
                'started_at' => $track->started_at?->format('d/m/Y H:i'),
                'ended_at' => $track->ended_at?->format('d/m/Y H:i'),
                'duration' => $track->duration_formatted,
                'segments_count' => $track->segments->count(),
            ],
        ];

        // Carica relazioni necessarie
        $track->load(['segments', 'competition', 'user']);

        // Se non ci sono segmenti, la traccia non è valida
        if ($track->segments->isEmpty()) {
            return $this->invalidate($track, 'La traccia non contiene segmenti validi.');
        }

        // Valida ogni segmento
        foreach ($track->segments as $segment) {
            $this->validateSegment($segment, $track);
        }

        // Calcola metriche finali
        $this->calculateFinalMetrics($track);

        // Verifica limiti della gara
        if ($track->competition) {
            $this->validateCompetitionRules($track);
        }

        // Determina stato finale
        if ($this->result['valid_distance_meters'] <= 0) {
            return $this->invalidate($track, 'Nessun segmento valido trovato.');
        }

        // Aggiorna la traccia con i risultati (solo se non è dry run)
        if (!$this->dryRun) {
            $this->updateTrackWithResults($track);
        }

        return $this->result;
    }

    /**
     * Valida un singolo segmento
     */
    protected function validateSegment(TrackSegment $segment, Track $track): void
    {
        $this->result['total_distance_meters'] += $segment->distance_meters;

        $validationDetails = [];
        $isValid = true;
        $isNotCredited = false;
        $rejectionReason = null;

        // 1. Controllo velocità
        $speedCheck = $this->checkSpeed($segment);
        $validationDetails['speed_check'] = $speedCheck;
        if (!$speedCheck['passed']) {
            $isValid = false;
            $rejectionReason = $speedCheck['reason'];
        }

        // 2. Controllo trasporto pubblico (treno/bus)
        if ($isValid && in_array($segment->transport_mode, [TransportMode::TRAIN, TransportMode::BUS])) {
            $publicTransportCheck = $this->checkPublicTransport($segment);
            $validationDetails['public_transport_check'] = $publicTransportCheck;
            if (!$publicTransportCheck['passed']) {
                $isValid = false;
                $rejectionReason = $publicTransportCheck['reason'];
            } elseif (!empty($publicTransportCheck['not_credited'])) {
                // Bus fuori PI/LI: valida per statistiche ma non genera crediti
                $isNotCredited = true;
                $rejectionReason = $publicTransportCheck['reason'];
            }
        }

        // 3. Auto/Moto: sempre "Non accreditata" (da Algoritmo.pdf)
        if ($isValid && in_array($segment->transport_mode, [TransportMode::CAR, TransportMode::MOTORCYCLE])) {
            $isNotCredited = true;
        }

        // 4. Controllo territoriale (se la gara ha restrizioni)
        if ($isValid && $track->competition) {
            $territoryCheck = $this->checkTerritory($segment, $track->competition);
            $validationDetails['territory_check'] = $territoryCheck;
            if (!$territoryCheck['passed']) {
                $isValid = false;
                $rejectionReason = $territoryCheck['reason'];
            }
        }

        // 5. Controllo modalità consentite dalla gara
        if ($isValid && $track->competition) {
            $modeCheck = $this->checkAllowedMode($segment, $track->competition);
            $validationDetails['mode_check'] = $modeCheck;
            if (!$modeCheck['passed']) {
                $isValid = false;
                $rejectionReason = $modeCheck['reason'];
            }
        }

        // Calcola crediti (solo se valido E non "non accreditata")
        $creditsPerKm = $this->getCreditsPerKmForMode($segment->transport_mode, $track->competition);
        $generatesCredits = $isValid && !$isNotCredited && $segment->transport_mode->generatesCredits();
        $credits = $generatesCredits ? $segment->calculateCredits($creditsPerKm) : 0;

        // Calcola tutte le metriche (emissioni, calorie, costi)
        $allMetrics = $this->emissionsCalculator->calculateAll($segment);

        // Prepara dettagli del segmento per il report
        $segmentDetail = [
            'sequence' => $segment->sequence,
            'transport_mode' => $segment->transport_mode->value,
            'transport_mode_label' => $segment->transport_mode->label(),
            'is_valid' => $isValid,
            'is_not_credited' => $isNotCredited,
            'rejection_reason' => $rejectionReason,
            'distance_meters' => $segment->distance_meters,
            'distance_km' => round($segment->distance_meters / 1000, 2),
            'duration' => $segment->duration_formatted,
            'avg_speed_kmh' => $segment->avg_speed_kmh ? round($segment->avg_speed_kmh, 1) : null,
            'max_speed_kmh' => $segment->max_speed_kmh ? round($segment->max_speed_kmh, 1) : null,
            'generates_credits' => $generatesCredits,
            'credits_earned' => round($credits, 4),
            'credits_euro_value' => $this->creditsToEuro($credits, $track->competition),
            'metrics' => $allMetrics,
            'start_coords' => [$segment->start_latitude, $segment->start_longitude],
            'end_coords' => [$segment->end_latitude, $segment->end_longitude],
            'validation_checks' => $validationDetails,
        ];

        $this->result['segments_details'][] = $segmentDetail;

        // Aggiorna il segmento nel database (solo se non è dry run)
        if ($isValid) {
            $segmentData = [
                'status' => $isNotCredited ? 'not_credited' : 'valid',
                'generates_credits' => $generatesCredits,
                'credits_earned' => $credits,
                // Emissioni risparmiate
                'co2_saved_grams' => $allMetrics['co2_saved_grams'],
                'so2_saved_mg' => $allMetrics['so2_saved_mg'],
                'nox_saved_grams' => $allMetrics['nox_saved_grams'],
                'co_saved_grams' => $allMetrics['co_saved_grams'],
                'pm10_saved_grams' => $allMetrics['pm10_saved_grams'],
                // Emissioni effettive
                'co2_emitted_grams' => $allMetrics['co2_emitted_grams'],
                'so2_emitted_mg' => $allMetrics['so2_emitted_mg'],
                'nox_emitted_grams' => $allMetrics['nox_emitted_grams'],
                'co_emitted_grams' => $allMetrics['co_emitted_grams'],
                'pm10_emitted_grams' => $allMetrics['pm10_emitted_grams'],
                // Calorie
                'calories_burned' => $allMetrics['calories_burned'],
                // Costi trasporto
                'cost_fuel_euros' => $allMetrics['cost_fuel_euros'],
                'cost_depreciation_euros' => $allMetrics['cost_depreciation_euros'],
                'cost_operation_euros' => $allMetrics['cost_operation_euros'],
                'cost_time_euros' => $allMetrics['cost_time_euros'],
                'cost_total_euros' => $allMetrics['cost_total_euros'],
                'validation_details' => $validationDetails,
            ];

            if ($isNotCredited) {
                $segmentData['rejection_reason'] = $rejectionReason;
            }

            if (!$this->dryRun) {
                $segment->update($segmentData);
            }

            $this->result['segments_validated']++;
            $this->result['valid_distance_meters'] += $segment->distance_meters;
            $this->result['credits_earned'] += $credits;
            $this->result['co2_saved_grams'] += $allMetrics['co2_saved_grams'];
            $this->result['calories_burned'] += $allMetrics['calories_burned'];

        } else {
            if (!$this->dryRun) {
                $segment->update([
                    'status' => 'invalid',
                    'generates_credits' => false,
                    'credits_earned' => 0,
                    'rejection_reason' => $rejectionReason,
                    'validation_details' => $validationDetails,
                ]);
            }

            $this->result['segments_invalid']++;
            $this->result['warnings'][] = "Segmento #{$segment->sequence}: {$rejectionReason}";
        }
    }

    /**
     * Controlla la velocità del segmento.
     *
     * Da Algoritmo.pdf:
     * - Piedi: dividere in pezzi di 500m, velocità max 18 km/h
     * - Bici: dividere in pezzi di 800m, velocità max 40 km/h
     *
     * Usa max_chunk_speed_kmh (calcolata dal parser sui pezzi) se disponibile,
     * altrimenti fallback sulla velocità media del segmento.
     */
    protected function checkSpeed(TrackSegment $segment): array
    {
        $maxSpeed = $segment->transport_mode->maxSpeedKmh();
        $checkDistance = $segment->transport_mode->speedCheckSegmentMeters();

        // Se non ci sono limiti per questa modalità, passa
        if ($maxSpeed === null) {
            return ['passed' => true, 'reason' => null];
        }

        // Verifica solo se il segmento è abbastanza lungo
        if ($segment->distance_meters < $checkDistance) {
            return ['passed' => true, 'reason' => null, 'note' => 'Segmento troppo corto per verifica'];
        }

        // Usa la velocità massima calcolata a pezzi (se disponibile dal parser)
        $maxChunkSpeedKmh = $segment->max_chunk_speed_kmh
            ? (float) $segment->max_chunk_speed_kmh
            : null;

        if ($maxChunkSpeedKmh !== null) {
            // Controllo a pezzi (conforme ad Algoritmo.pdf)
            if ($maxChunkSpeedKmh > $maxSpeed) {
                return [
                    'passed' => false,
                    'reason' => sprintf(
                        'Velocità %.1f km/h in un pezzo di %dm superiore al limite di %.0f km/h per %s',
                        $maxChunkSpeedKmh,
                        $checkDistance,
                        $maxSpeed,
                        $segment->transport_mode->label()
                    ),
                    'max_chunk_speed_kmh' => $maxChunkSpeedKmh,
                    'max_allowed_kmh' => $maxSpeed,
                    'chunk_size_meters' => $checkDistance,
                    'speed_chunks' => $segment->speed_chunks,
                ];
            }

            return [
                'passed' => true,
                'reason' => null,
                'max_chunk_speed_kmh' => $maxChunkSpeedKmh,
                'max_allowed_kmh' => $maxSpeed,
                'chunk_size_meters' => $checkDistance,
            ];
        }

        // Fallback: velocità media del segmento (per tracce importate senza dati chunk)
        $avgSpeedKmh = $segment->avg_speed_kmh;

        if ($avgSpeedKmh && $avgSpeedKmh > $maxSpeed) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'Velocità media %.1f km/h superiore al limite di %.0f km/h per %s (fallback)',
                    $avgSpeedKmh,
                    $maxSpeed,
                    $segment->transport_mode->label()
                ),
                'avg_speed_kmh' => $avgSpeedKmh,
                'max_allowed_kmh' => $maxSpeed,
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
            'avg_speed_kmh' => $avgSpeedKmh,
            'max_allowed_kmh' => $maxSpeed,
        ];
    }

    /**
     * Verifica la prossimità a stazioni/fermate per treno/bus
     */
    protected function checkPublicTransport(TrackSegment $segment): array
    {
        if ($segment->transport_mode === TransportMode::TRAIN) {
            return $this->checkTrainStation($segment);
        }

        if ($segment->transport_mode === TransportMode::BUS) {
            return $this->checkBusStop($segment);
        }

        return ['passed' => true, 'reason' => null];
    }

    /**
     * Verifica prossimità a stazioni treno (inizio e fine del segmento)
     */
    protected function checkTrainStation(TrackSegment $segment): array
    {
        // Verifica stazione vicino al punto di partenza
        $startStation = TrainStation::active()
            ->nearby($segment->start_latitude, $segment->start_longitude, self::TRAIN_STATION_RADIUS)
            ->first();

        if (!$startStation) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'Nessuna stazione ferroviaria trovata entro %dm dal punto di partenza',
                    self::TRAIN_STATION_RADIUS
                ),
            ];
        }

        // Verifica stazione vicino al punto di arrivo
        $endStation = TrainStation::active()
            ->nearby($segment->end_latitude, $segment->end_longitude, self::TRAIN_STATION_RADIUS)
            ->first();

        if (!$endStation) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'Nessuna stazione ferroviaria trovata entro %dm dal punto di arrivo',
                    self::TRAIN_STATION_RADIUS
                ),
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
            'start_station' => $startStation->name,
            'end_station' => $endStation->name,
        ];
    }

    /**
     * Verifica prossimità a fermate bus (solo Pisa/Livorno)
     */
    protected function checkBusStop(TrackSegment $segment): array
    {
        // Prima verifica se siamo in provincia di Pisa o Livorno
        $municipality = $this->findMunicipalityByCoordinates(
            $segment->start_latitude,
            $segment->start_longitude
        );

        if (!$municipality) {
            // Se non troviamo il comune, assumiamo che la verifica non sia applicabile
            return [
                'passed' => true,
                'reason' => null,
                'note' => 'Verifica fermate bus non applicabile (comune non identificato)',
            ];
        }

        $provinceCode = $municipality->province?->code ?? '';

        // Se non siamo in PI o LI, la traccia bus diventa "Non accreditata" (da Algoritmo.pdf)
        if (!in_array($provinceCode, self::BUS_PROVINCES)) {
            return [
                'passed' => true,
                'not_credited' => true,
                'reason' => 'Traccia bus fuori dalle province di Pisa/Livorno: non accreditata',
            ];
        }

        // Verifica fermata vicino al punto di partenza
        $startStop = BusStop::active()
            ->nearby($segment->start_latitude, $segment->start_longitude, self::BUS_STOP_RADIUS)
            ->first();

        if (!$startStop) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'Nessuna fermata bus trovata entro %dm dal punto di partenza',
                    self::BUS_STOP_RADIUS
                ),
            ];
        }

        // Verifica fermata vicino al punto di arrivo
        $endStop = BusStop::active()
            ->nearby($segment->end_latitude, $segment->end_longitude, self::BUS_STOP_RADIUS)
            ->first();

        if (!$endStop) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'Nessuna fermata bus trovata entro %dm dal punto di arrivo',
                    self::BUS_STOP_RADIUS
                ),
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
            'start_stop' => $startStop->name,
            'end_stop' => $endStop->name,
        ];
    }

    /**
     * Verifica che il segmento sia nel territorio consentito dalla gara
     */
    protected function checkTerritory(TrackSegment $segment, Competition $competition): array
    {
        // Se la gara non ha restrizioni territoriali, passa
        $hasRestrictions = !empty($competition->allowed_municipality_ids)
            || !empty($competition->allowed_province_ids)
            || !empty($competition->allowed_region_ids);

        if (!$hasRestrictions) {
            return ['passed' => true, 'reason' => null];
        }

        // Trova il comune dalle coordinate del punto di partenza
        $municipality = $this->findMunicipalityByCoordinates(
            $segment->start_latitude,
            $segment->start_longitude
        );

        if (!$municipality) {
            // Proviamo con reverse geocoding OSM
            $municipality = $this->reverseGeocodeToMunicipality(
                $segment->start_latitude,
                $segment->start_longitude
            );
        }

        if (!$municipality) {
            return [
                'passed' => false,
                'reason' => 'Impossibile determinare il territorio del segmento',
            ];
        }

        // Verifica se il comune è consentito
        if (!empty($competition->allowed_municipality_ids)) {
            if (in_array($municipality->id, $competition->allowed_municipality_ids)) {
                return ['passed' => true, 'reason' => null, 'municipality' => $municipality->name];
            }
        }

        // Verifica se la provincia è consentita
        if (!empty($competition->allowed_province_ids)) {
            if (in_array($municipality->province_id, $competition->allowed_province_ids)) {
                return ['passed' => true, 'reason' => null, 'municipality' => $municipality->name];
            }
        }

        // Verifica se la regione è consentita
        if (!empty($competition->allowed_region_ids)) {
            $region = $municipality->province?->region;
            if ($region && in_array($region->id, $competition->allowed_region_ids)) {
                return ['passed' => true, 'reason' => null, 'municipality' => $municipality->name];
            }
        }

        return [
            'passed' => false,
            'reason' => sprintf(
                'Il comune %s non rientra nel territorio consentito dalla gara',
                $municipality->name
            ),
        ];
    }

    /**
     * Verifica che la modalità di trasporto sia consentita dalla gara
     */
    protected function checkAllowedMode(TrackSegment $segment, Competition $competition): array
    {
        if (empty($competition->allowed_transport_modes)) {
            return ['passed' => true, 'reason' => null];
        }

        if (in_array($segment->transport_mode->value, $competition->allowed_transport_modes)) {
            return ['passed' => true, 'reason' => null];
        }

        return [
            'passed' => false,
            'reason' => sprintf(
                'La modalità %s non è consentita in questa gara',
                $segment->transport_mode->label()
            ),
        ];
    }

    /**
     * Trova il comune più vicino alle coordinate date
     */
    protected function findMunicipalityByCoordinates(float $lat, float $lng): ?Municipality
    {
        // Cerca comuni nel raggio di ~5km
        $radiusKm = 5;
        $latDelta = $radiusKm / 111;
        $lngDelta = $radiusKm / (111 * cos(deg2rad($lat)));

        return Municipality::active()
            ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta])
            ->orderByRaw("(latitude - ?) * (latitude - ?) + (longitude - ?) * (longitude - ?)", [
                $lat, $lat, $lng, $lng
            ])
            ->first();
    }

    /**
     * Usa OpenStreetMap Nominatim per reverse geocoding
     */
    protected function reverseGeocodeToMunicipality(float $lat, float $lng): ?Municipality
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'GoodGo/1.0',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                    'addressdetails' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];

                // Cerca il comune nel nostro database
                $municipalityName = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? null;

                if ($municipalityName) {
                    return Municipality::where('name', 'like', '%' . $municipalityName . '%')
                        ->active()
                        ->first();
                }
            }
        } catch (\Exception $e) {
            Log::channel('tracks')->warning("Reverse geocoding failed: {$e->getMessage()}", [
                'lat' => $lat,
                'lng' => $lng,
            ]);
        }

        return null;
    }

    /**
     * Calcola le metriche finali dopo la validazione dei segmenti
     */
    protected function calculateFinalMetrics(Track $track): void
    {
        $track->refresh();
        $competition = $track->competition;

        if (!$competition) {
            return;
        }

        // === 1. Verifica limite tracce giornaliere ===
        if ($competition->max_daily_tracks) {
            $todayTracks = Track::where('user_id', $track->user_id)
                ->where('competition_id', $track->competition_id)
                ->whereDate('started_at', $track->started_at->toDateString())
                ->where('status', TrackStatus::VALID)
                ->where('id', '!=', $track->id)
                ->count();

            if ($todayTracks >= $competition->max_daily_tracks) {
                $this->result['warnings'][] = 'Limite tracce giornaliere raggiunto. I crediti non saranno assegnati.';
                $this->result['credits_earned'] = 0;
            }
        }

        // === 2. Verifica distanza minima globale della gara ===
        $validDistanceKm = $this->result['valid_distance_meters'] / 1000;

        if ($competition->min_track_distance && $validDistanceKm < $competition->min_track_distance) {
            $this->result['warnings'][] = sprintf(
                'Distanza valida (%.2f km) inferiore al minimo richiesto (%.2f km)',
                $validDistanceKm,
                $competition->min_track_distance
            );
            $this->result['credits_earned'] = 0;
        }

        // === 3. Verifica distanza massima globale della gara ===
        if ($competition->max_track_distance && $validDistanceKm > $competition->max_track_distance) {
            $this->result['warnings'][] = sprintf(
                'Distanza (%.2f km) superiore al massimo globale (%.2f km). Crediti limitati.',
                $validDistanceKm,
                $competition->max_track_distance
            );
            $this->capCreditsToMaxDistance($competition->max_track_distance, $track);
        }

        // === 4. Distanza massima per traccia per modalità (da Documentazione pag. 10) ===
        $maxDistancePerMode = $competition->max_distance_per_mode;
        if (!empty($maxDistancePerMode)) {
            $this->applyMaxDistancePerMode($maxDistancePerMode, $track);
        }

        // === 5. Distanza massima giornaliera per modalità (da Documentazione pag. 10) ===
        $maxDailyDistancePerMode = $competition->max_daily_distance_per_mode;
        if (!empty($maxDailyDistancePerMode)) {
            $this->applyMaxDailyDistancePerMode($maxDailyDistancePerMode, $track);
        }
    }

    /**
     * Limita i crediti alla distanza massima globale
     */
    protected function capCreditsToMaxDistance(float $maxDistanceKm, Track $track): void
    {
        // Ricalcola i crediti basandosi solo sulla distanza massima
        $creditsOnMaxDistance = 0;
        $distanceUsed = [];

        foreach ($this->result['segments_details'] as $detail) {
            if (!$detail['generates_credits']) {
                continue;
            }

            $mode = $detail['transport_mode'];
            $distanceUsed[$mode] = ($distanceUsed[$mode] ?? 0) + $detail['distance_km'];

            // Calcola la distanza effettiva da accreditare
            $totalModeKm = $distanceUsed[$mode];
            $prevModeKm = $totalModeKm - $detail['distance_km'];

            // Se già oltre il limite globale per questa modalità, 0 crediti
            $globalUsed = array_sum($distanceUsed) - $detail['distance_km'];
            if ($globalUsed >= $maxDistanceKm) {
                continue;
            }

            $availableKm = $maxDistanceKm - $globalUsed;
            $creditableKm = min($detail['distance_km'], $availableKm);

            $creditsPerKm = $this->getCreditsPerKmForMode(
                TransportMode::from($mode),
                $track->competition
            );
            $creditsOnMaxDistance += $creditableKm * $creditsPerKm;
        }

        $this->result['credits_earned'] = min($this->result['credits_earned'], $creditsOnMaxDistance);
    }

    /**
     * Applica limiti distanza per traccia per modalità (da Documentazione: "Distanza massima ammesse per traccia")
     */
    protected function applyMaxDistancePerMode(array $maxDistancePerMode, Track $track): void
    {
        // Calcola distanza per modalità dai segmenti validati
        $distanceByMode = [];
        foreach ($this->result['segments_details'] as $detail) {
            if ($detail['is_valid']) {
                $mode = $detail['transport_mode'];
                $distanceByMode[$mode] = ($distanceByMode[$mode] ?? 0) + $detail['distance_km'];
            }
        }

        foreach ($distanceByMode as $mode => $distanceKm) {
            $maxKm = $maxDistancePerMode[$mode] ?? null;

            // null = senza limiti
            if ($maxKm === null || $maxKm == 0) {
                continue;
            }

            if ($distanceKm > $maxKm) {
                $excessKm = $distanceKm - $maxKm;
                $creditsPerKm = $this->getCreditsPerKmForMode(
                    TransportMode::from($mode),
                    $track->competition
                );
                $excessCredits = $excessKm * $creditsPerKm;
                $this->result['credits_earned'] = max(0, $this->result['credits_earned'] - $excessCredits);

                $this->result['warnings'][] = sprintf(
                    'Modalità %s: distanza %.2f km superiore al massimo per traccia (%.2f km). Crediti limitati.',
                    TransportMode::from($mode)->label(),
                    $distanceKm,
                    $maxKm
                );
            }
        }
    }

    /**
     * Applica limiti distanza giornaliera per modalità (da Documentazione: "Distanza massima ammesse al giorno")
     */
    protected function applyMaxDailyDistancePerMode(array $maxDailyDistancePerMode, Track $track): void
    {
        if (!$track->started_at) {
            return;
        }

        // Calcola distanza per modalità dai segmenti validati di questa traccia
        $distanceByMode = [];
        foreach ($this->result['segments_details'] as $detail) {
            if ($detail['is_valid']) {
                $mode = $detail['transport_mode'];
                $distanceByMode[$mode] = ($distanceByMode[$mode] ?? 0) + $detail['distance_km'];
            }
        }

        foreach ($distanceByMode as $mode => $distanceKm) {
            $maxDailyKm = $maxDailyDistancePerMode[$mode] ?? null;

            if ($maxDailyKm === null || $maxDailyKm == 0) {
                continue;
            }

            // Calcola distanza già accumulata oggi per questa modalità (tracce già validate)
            $todayDistanceKm = $this->getTodayDistanceForMode(
                $track->user_id,
                $track->competition_id,
                $track->started_at->toDateString(),
                $mode,
                $track->id
            );

            $totalDailyKm = $todayDistanceKm + $distanceKm;

            if ($totalDailyKm > $maxDailyKm) {
                $remainingKm = max(0, $maxDailyKm - $todayDistanceKm);
                $excessKm = $distanceKm - $remainingKm;

                if ($excessKm > 0) {
                    $creditsPerKm = $this->getCreditsPerKmForMode(
                        TransportMode::from($mode),
                        $track->competition
                    );
                    $excessCredits = $excessKm * $creditsPerKm;
                    $this->result['credits_earned'] = max(0, $this->result['credits_earned'] - $excessCredits);

                    $this->result['warnings'][] = sprintf(
                        'Modalità %s: distanza giornaliera %.2f km (di cui %.2f oggi) superiore al limite giornaliero (%.2f km). Crediti limitati.',
                        TransportMode::from($mode)->label(),
                        $totalDailyKm,
                        $todayDistanceKm,
                        $maxDailyKm
                    );
                }
            }
        }
    }

    /**
     * Calcola la distanza percorsa oggi per una data modalità (tracce già validate, esclusa la corrente)
     */
    protected function getTodayDistanceForMode(
        int $userId,
        int $competitionId,
        string $date,
        string $mode,
        int $excludeTrackId
    ): float {
        return Track::where('user_id', $userId)
            ->where('competition_id', $competitionId)
            ->whereDate('started_at', $date)
            ->whereIn('status', [TrackStatus::VALID, TrackStatus::NOT_CREDITED])
            ->where('id', '!=', $excludeTrackId)
            ->with('segments')
            ->get()
            ->flatMap(fn($t) => $t->segments)
            ->where('transport_mode', TransportMode::from($mode))
            ->where('status', 'valid')
            ->sum('distance_meters') / 1000;
    }

    /**
     * Determina i crediti per km per una data modalità dalla gara.
     * Usa credits_per_mode se disponibile, altrimenti credits_per_km * credits_multiplier.
     */
    protected function getCreditsPerKmForMode(TransportMode $mode, ?Competition $competition): float
    {
        if (!$competition) {
            return 1.0;
        }

        // Priorità: credits_per_mode specifico per la modalità
        $creditsPerMode = $competition->credits_per_mode;
        if (!empty($creditsPerMode) && isset($creditsPerMode[$mode->value])) {
            $perModeValue = (float) $creditsPerMode[$mode->value];
            // Il moltiplicatore si applica anche ai crediti per modalità
            $multiplier = (float) ($competition->credits_multiplier ?? 1.0);
            return $perModeValue * $multiplier;
        }

        // Fallback: credits_per_km globale * moltiplicatore
        $creditsPerKm = (float) ($competition->credits_per_km ?? 1.0);
        $multiplier = (float) ($competition->credits_multiplier ?? 1.0);
        return $creditsPerKm * $multiplier;
    }

    /**
     * Converte crediti in euro usando il tasso della gara (credits_to_euro = crediti per 1€)
     */
    protected function creditsToEuro(float $credits, ?Competition $competition): float
    {
        if (!$competition || !$competition->credits_to_euro || $competition->credits_to_euro <= 0) {
            return 0;
        }

        return round($credits / (float) $competition->credits_to_euro, 2);
    }

    /**
     * Verifica le regole specifiche della gara
     */
    protected function validateCompetitionRules(Track $track): void
    {
        // Verifica che la gara sia in corso
        if (!$track->competition->isRunning()) {
            $this->result['warnings'][] = 'La gara non è attualmente in corso. I crediti non saranno assegnati.';
            $this->result['credits_earned'] = 0;
        }

        // Verifica che l'utente sia iscritto e approvato
        if (!$track->competition->hasApprovedUser($track->user)) {
            $this->result['warnings'][] = 'Utente non iscritto o non approvato per questa gara. I crediti non saranno assegnati.';
            $this->result['credits_earned'] = 0;
        }
    }

    /**
     * Aggiorna la traccia con i risultati della validazione
     */
    protected function updateTrackWithResults(Track $track): void
    {
        // Aggrega metriche da tutti i segmenti validati
        $segmentDetails = $this->result['segments_details'] ?? [];
        $aggregated = [
            'co2_saved_grams' => 0, 'so2_saved_mg' => 0, 'nox_saved_grams' => 0,
            'co_saved_grams' => 0, 'pm10_saved_grams' => 0,
            'co2_emitted_grams' => 0, 'so2_emitted_mg' => 0, 'nox_emitted_grams' => 0,
            'co_emitted_grams' => 0, 'pm10_emitted_grams' => 0,
            'calories_burned' => 0,
            'cost_fuel_euros' => 0, 'cost_depreciation_euros' => 0,
            'cost_operation_euros' => 0, 'cost_time_euros' => 0, 'cost_total_euros' => 0,
        ];

        foreach ($segmentDetails as $detail) {
            if ($detail['is_valid'] && isset($detail['metrics'])) {
                foreach ($aggregated as $key => &$value) {
                    $value += $detail['metrics'][$key] ?? 0;
                }
            }
        }

        // Determina lo stato finale della traccia
        $hasNotCredited = collect($segmentDetails)->contains('is_not_credited', true);
        $hasValid = collect($segmentDetails)->contains(fn($d) => $d['is_valid'] && !($d['is_not_credited'] ?? false));

        if ($hasValid) {
            $status = TrackStatus::VALID;
        } elseif ($hasNotCredited) {
            $status = TrackStatus::NOT_CREDITED;
        } else {
            $status = TrackStatus::INVALID;
        }

        $track->update([
            'status' => $status,
            'rejection_reason' => $this->result['rejection_reason'],
            'validation_warnings' => $this->result['warnings'],
            'validated_at' => now(),
            'total_distance_meters' => $this->result['total_distance_meters'],
            'valid_distance_meters' => $this->result['valid_distance_meters'],
            'credits_earned' => $this->result['credits_earned'],
            // Emissioni risparmiate
            'co2_saved_grams' => $aggregated['co2_saved_grams'],
            'so2_saved_mg' => $aggregated['so2_saved_mg'],
            'nox_saved_grams' => $aggregated['nox_saved_grams'],
            'co_saved_grams' => $aggregated['co_saved_grams'],
            'pm10_saved_grams' => $aggregated['pm10_saved_grams'],
            // Emissioni effettive
            'co2_emitted_grams' => $aggregated['co2_emitted_grams'],
            'so2_emitted_mg' => $aggregated['so2_emitted_mg'],
            'nox_emitted_grams' => $aggregated['nox_emitted_grams'],
            'co_emitted_grams' => $aggregated['co_emitted_grams'],
            'pm10_emitted_grams' => $aggregated['pm10_emitted_grams'],
            // Calorie
            'calories_burned' => $aggregated['calories_burned'],
            // Costi trasporto
            'cost_fuel_euros' => $aggregated['cost_fuel_euros'],
            'cost_depreciation_euros' => $aggregated['cost_depreciation_euros'],
            'cost_operation_euros' => $aggregated['cost_operation_euros'],
            'cost_time_euros' => $aggregated['cost_time_euros'],
            'cost_total_euros' => $aggregated['cost_total_euros'],
        ]);

        // Aggiorna saldo crediti utente tramite CreditService
        if ($this->result['credits_earned'] > 0) {
            $this->creditService->addCredits(
                $track->user,
                $this->result['credits_earned'],
                \App\Enums\CreditLogType::TRACK_VALIDATION,
                "Validazione traccia #{$track->id}",
                $track,
                [
                    'track_id' => $track->id,
                    'competition_id' => $track->competition_id,
                    'valid_distance_km' => $this->result['valid_distance_meters'] / 1000,
                ]
            );
        }

        // Aggiorna statistiche gara
        if ($track->competition && $this->result['is_valid']) {
            $this->updateCompetitionStats($track);
        }

        // Controlla e assegna badge (solo per tracce valide)
        if ($this->result['is_valid']) {
            $this->badgeService->checkAndAward($track->user);
        }
    }

    /**
     * Aggiorna le statistiche della gara e dell'utente nella gara
     */
    protected function updateCompetitionStats(Track $track): void
    {
        // Aggiorna pivot competition_user
        $pivotData = $track->competition->users()
            ->where('user_id', $track->user_id)
            ->first()?->pivot;

        if ($pivotData) {
            $track->competition->users()->updateExistingPivot($track->user_id, [
                'tracks_count' => ($pivotData->tracks_count ?? 0) + 1,
                'total_distance_km' => ($pivotData->total_distance_km ?? 0) + ($this->result['valid_distance_meters'] / 1000),
                'total_credits' => ($pivotData->total_credits ?? 0) + $this->result['credits_earned'],
                'total_co2_saved_kg' => ($pivotData->total_co2_saved_kg ?? 0) + ($this->result['co2_saved_grams'] / 1000),
            ]);
        }

        // Aggiorna statistiche cache della gara
        $track->competition->updateStatistics();
        $track->competition->recalculateRanks();
    }

    /**
     * Invalida la traccia con un motivo
     */
    protected function invalidate(Track $track, string $reason): array
    {
        $this->result['is_valid'] = false;
        $this->result['rejection_reason'] = $reason;

        // Salva nel database solo se non è dry run
        if (!$this->dryRun) {
            $track->markAsInvalid($reason);
        }

        return $this->result;
    }
}
