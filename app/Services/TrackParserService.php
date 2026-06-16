<?php

namespace App\Services;

use App\Enums\TransportMode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TrackParserService
{
    /**
     * Accuratezza GPS massima consentita in metri (da Algoritmo.pdf)
     */
    public const MAX_GPS_ACCURACY_METERS = 20.0;

    /**
     * Velocità GPS massima consentita in m/s (da Algoritmo.pdf)
     */
    public const MAX_GPS_SPEED_MS = 150.0;

    /**
     * Struttura di un punto GPS parsato
     */
    public const POINT_KEYS = [
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'timestamp',
        'vehicle_mode',
        'elevation',
        'session_id',
    ];

    /**
     * Parsa il contenuto di un file CSV traccia
     *
     * @param string $content Il contenuto del file CSV
     * @return array{session_id: string, points: array, segments: array, summary: array}
     */
    public function parse(string $content): array
    {
        $lines = explode("\n", trim($content));

        if (count($lines) < 2) {
            throw new \InvalidArgumentException('File traccia vuoto o non valido');
        }

        // Parse header
        $header = str_getcsv(rtrim($lines[0], ','));
        $headerMap = array_flip($header);

        // Verifica colonne richieste
        $requiredColumns = ['latitude', 'longitude', 'accuracy', 'speed', 'timeStamp', 'vehicleMode', 'sessionId'];
        foreach ($requiredColumns as $col) {
            if (!isset($headerMap[$col])) {
                throw new \InvalidArgumentException("Colonna richiesta mancante: {$col}");
            }
        }

        // Parse points
        $points = [];
        $sessionId = null;
        $unrecognizedModes = [];

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue;
            }

            $values = str_getcsv(rtrim($line, ','));

            // Salta righe incomplete
            if (count($values) < count($header)) {
                continue;
            }

            $latitude = (float) $values[$headerMap['latitude']];
            $longitude = (float) $values[$headerMap['longitude']];

            // Salta punti con coordinate non valide
            if ($latitude === 0.0 && $longitude === 0.0) {
                continue;
            }

            // Risolve la modalità dal valore grezzo: accetta sia i codici
            // numerici (es. "2") sia le label testuali (es. "bike") che alcune
            // versioni dell'app inviano. Se non riconosciuto, fallback a piedi
            // e raccolta del valore per il log.
            $rawVehicleMode = (string) $values[$headerMap['vehicleMode']];
            $mode = TransportMode::fromTrackValue($rawVehicleMode);
            if ($mode === null) {
                $key = trim($rawVehicleMode);
                $unrecognizedModes[$key] = ($unrecognizedModes[$key] ?? 0) + 1;
                $mode = TransportMode::WALK;
            }

            $point = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => (float) $values[$headerMap['accuracy']],
                'speed' => (float) $values[$headerMap['speed']],
                'timestamp' => (int) $values[$headerMap['timeStamp']],
                'vehicle_mode' => $mode->toVehicleMode(),
                'elevation' => isset($headerMap['elevation']) ? (float) $values[$headerMap['elevation']] : null,
                'session_id' => $values[$headerMap['sessionId']],
            ];

            if ($sessionId === null) {
                $sessionId = $point['session_id'];
            }

            $points[] = $point;
        }

        if (!empty($unrecognizedModes)) {
            Log::warning('vehicleMode non riconosciuto nel file traccia, declassato a piedi', [
                'session_id' => $sessionId,
                'values' => $unrecognizedModes,
            ]);
        }

        if (empty($points)) {
            throw new \InvalidArgumentException('Nessun punto GPS valido trovato');
        }

        // Ordina per timestamp
        usort($points, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

        // Pulizia dati (da Algoritmo.pdf):
        // 1. Rimuovi punti con accuratezza GPS > 20 metri
        $points = $this->filterByAccuracy($points, self::MAX_GPS_ACCURACY_METERS);

        // 2. Rimuovi punti con velocità eccessiva > 150 m/s
        $points = $this->filterByMaxAbsoluteSpeed($points, self::MAX_GPS_SPEED_MS);

        if (empty($points)) {
            throw new \InvalidArgumentException('Nessun punto GPS valido dopo la pulizia dati');
        }

        // Identifica i segmenti
        $segments = $this->identifySegments($points);

        // Calcola il riepilogo
        $summary = $this->calculateSummary($points, $segments);
        $summary['session_id'] = $sessionId;

        return [
            'session_id' => $sessionId,
            'points' => $points,
            'segments' => $segments,
            'summary' => $summary,
        ];
    }

    /**
     * Identifica i segmenti basandosi sul cambio di modalità di trasporto
     */
    protected function identifySegments(array $points): array
    {
        if (empty($points)) {
            return [];
        }

        $segments = [];
        $currentSegment = [
            'sequence' => 0,
            'vehicle_mode' => $points[0]['vehicle_mode'],
            'transport_mode' => TransportMode::fromVehicleMode($points[0]['vehicle_mode']),
            'points' => [$points[0]],
            'start_index' => 0,
        ];

        for ($i = 1; $i < count($points); $i++) {
            $point = $points[$i];

            if ($point['vehicle_mode'] !== $currentSegment['vehicle_mode']) {
                // Chiudi il segmento corrente
                $currentSegment['end_index'] = $i - 1;
                $currentSegment = $this->calculateSegmentMetrics($currentSegment);
                $segments[] = $currentSegment;

                // Inizia nuovo segmento
                $currentSegment = [
                    'sequence' => count($segments),
                    'vehicle_mode' => $point['vehicle_mode'],
                    'transport_mode' => TransportMode::fromVehicleMode($point['vehicle_mode']),
                    'points' => [$point],
                    'start_index' => $i,
                ];
            } else {
                $currentSegment['points'][] = $point;
            }
        }

        // Chiudi l'ultimo segmento
        $currentSegment['end_index'] = count($points) - 1;
        $currentSegment = $this->calculateSegmentMetrics($currentSegment);
        $segments[] = $currentSegment;

        return $segments;
    }

    /**
     * Calcola le metriche per un singolo segmento
     */
    protected function calculateSegmentMetrics(array $segment): array
    {
        $points = $segment['points'];
        $pointCount = count($points);

        if ($pointCount === 0) {
            return $segment;
        }

        $firstPoint = $points[0];
        $lastPoint = $points[$pointCount - 1];

        // Coordinate inizio/fine
        $segment['start_latitude'] = $firstPoint['latitude'];
        $segment['start_longitude'] = $firstPoint['longitude'];
        $segment['end_latitude'] = $lastPoint['latitude'];
        $segment['end_longitude'] = $lastPoint['longitude'];

        // Tempi
        $segment['started_at'] = Carbon::createFromTimestampMs($firstPoint['timestamp']);
        $segment['ended_at'] = Carbon::createFromTimestampMs($lastPoint['timestamp']);
        $segment['duration_seconds'] = (int) (($lastPoint['timestamp'] - $firstPoint['timestamp']) / 1000);

        // Calcola distanza totale
        $totalDistance = 0;
        $speeds = [];
        $accuracies = [];

        for ($i = 1; $i < $pointCount; $i++) {
            $distance = $this->vincentyDistance(
                $points[$i - 1]['latitude'],
                $points[$i - 1]['longitude'],
                $points[$i]['latitude'],
                $points[$i]['longitude']
            );
            $totalDistance += $distance;

            if ($points[$i]['speed'] > 0) {
                $speeds[] = $points[$i]['speed'];
            }

            $accuracies[] = $points[$i]['accuracy'];
        }

        $segment['distance_meters'] = (int) round($totalDistance);
        $segment['points_count'] = $pointCount;

        // Velocità media e massima
        if (!empty($speeds)) {
            $segment['avg_speed_ms'] = array_sum($speeds) / count($speeds);
            $segment['max_speed_ms'] = max($speeds);
        } else {
            $segment['avg_speed_ms'] = null;
            $segment['max_speed_ms'] = null;
        }

        // Accuratezza media
        if (!empty($accuracies)) {
            $segment['avg_accuracy_meters'] = array_sum($accuracies) / count($accuracies);
        } else {
            $segment['avg_accuracy_meters'] = null;
        }

        // Genera credits se la modalità lo permette
        $segment['generates_credits'] = $segment['transport_mode']->generatesCredits();

        // Calcolo velocità a pezzi (da Algoritmo.pdf):
        // Piedi: pezzi di 500m, Bici: pezzi di 800m
        $chunkSize = $segment['transport_mode']->speedCheckSegmentMeters();
        if ($chunkSize !== null && $totalDistance >= $chunkSize) {
            $chunkAnalysis = $this->calculateChunkSpeeds($points, $chunkSize);
            $segment['max_chunk_speed_kmh'] = $chunkAnalysis['max_speed_kmh'];
            $segment['speed_chunks'] = $chunkAnalysis['chunks'];
        } else {
            $segment['max_chunk_speed_kmh'] = null;
            $segment['speed_chunks'] = null;
        }

        // Polyline semplificata (ogni N punti)
        $segment['polyline'] = $this->generateSimplifiedPolyline($points, 10);

        return $segment;
    }

    /**
     * Calcola il riepilogo generale della traccia
     */
    protected function calculateSummary(array $points, array $segments): array
    {
        $firstPoint = $points[0];
        $lastPoint = $points[count($points) - 1];

        $totalDistance = array_sum(array_column($segments, 'distance_meters'));
        $totalDuration = (int) (($lastPoint['timestamp'] - $firstPoint['timestamp']) / 1000);

        // Calcola distanza valida (solo segmenti che generano crediti)
        $validDistance = 0;
        foreach ($segments as $segment) {
            if ($segment['generates_credits']) {
                $validDistance += $segment['distance_meters'];
            }
        }

        // Determina se multimodale
        $transportModes = array_unique(array_map(
            fn($s) => $s['transport_mode']->value,
            $segments
        ));
        $isMultimodal = count($transportModes) > 1;

        // Modalità principale (quella con più distanza)
        $distanceByMode = [];
        foreach ($segments as $segment) {
            $mode = $segment['transport_mode']->value;
            $distanceByMode[$mode] = ($distanceByMode[$mode] ?? 0) + $segment['distance_meters'];
        }
        arsort($distanceByMode);
        $primaryMode = array_key_first($distanceByMode);

        // Velocità medie
        $allSpeeds = [];
        $allAccuracies = [];
        foreach ($segments as $segment) {
            if ($segment['avg_speed_ms'] !== null) {
                $allSpeeds[] = $segment['avg_speed_ms'];
            }
            if ($segment['avg_accuracy_meters'] !== null) {
                $allAccuracies[] = $segment['avg_accuracy_meters'];
            }
        }

        return [
            'started_at' => Carbon::createFromTimestampMs($firstPoint['timestamp']),
            'ended_at' => Carbon::createFromTimestampMs($lastPoint['timestamp']),
            'duration_seconds' => $totalDuration,
            'start_latitude' => $firstPoint['latitude'],
            'start_longitude' => $firstPoint['longitude'],
            'end_latitude' => $lastPoint['latitude'],
            'end_longitude' => $lastPoint['longitude'],
            'total_distance_meters' => $totalDistance,
            'valid_distance_meters' => $validDistance,
            'is_multimodal' => $isMultimodal,
            'primary_transport_mode' => TransportMode::from($primaryMode),
            'points_count' => count($points),
            'segments_count' => count($segments),
            'avg_speed_ms' => !empty($allSpeeds) ? array_sum($allSpeeds) / count($allSpeeds) : null,
            'avg_accuracy_meters' => !empty($allAccuracies) ? array_sum($allAccuracies) / count($allAccuracies) : null,
        ];
    }

    /**
     * Divide il tracciato in pezzi di N metri e calcola la velocità di ogni pezzo.
     * Da Algoritmo.pdf: "Dividere il tracciato in pezzi di 500 metri (piedi) / 800 metri (bici),
     * calcolare la velocità tra l'inizio e la fine, se supera il limite la traccia non è valida."
     *
     * @param array $points Punti GPS del segmento
     * @param int $chunkSizeMeters Dimensione del pezzo in metri (500 per piedi, 800 per bici)
     * @return array{max_speed_kmh: float, chunks: array}
     */
    public function calculateChunkSpeeds(array $points, int $chunkSizeMeters): array
    {
        $chunks = [];
        $maxSpeedKmh = 0;

        $pointCount = count($points);
        if ($pointCount < 2) {
            return ['max_speed_kmh' => 0, 'chunks' => []];
        }

        // Percorri i punti e crea pezzi della dimensione richiesta
        $chunkStartIndex = 0;
        $chunkDistance = 0;

        for ($i = 1; $i < $pointCount; $i++) {
            $distance = $this->vincentyDistance(
                $points[$i - 1]['latitude'],
                $points[$i - 1]['longitude'],
                $points[$i]['latitude'],
                $points[$i]['longitude']
            );
            $chunkDistance += $distance;

            // Quando raggiungiamo la dimensione del pezzo, calcola la velocità
            if ($chunkDistance >= $chunkSizeMeters) {
                $chunkStart = $points[$chunkStartIndex];
                $chunkEnd = $points[$i];

                // Tempo del pezzo in secondi
                $timeDeltaMs = $chunkEnd['timestamp'] - $chunkStart['timestamp'];
                $timeDeltaSeconds = $timeDeltaMs / 1000;

                if ($timeDeltaSeconds > 0) {
                    // Velocità = distanza / tempo
                    $speedMs = $chunkDistance / $timeDeltaSeconds;
                    $speedKmh = $speedMs * 3.6;

                    $chunks[] = [
                        'start_index' => $chunkStartIndex,
                        'end_index' => $i,
                        'distance_meters' => round($chunkDistance, 1),
                        'duration_seconds' => round($timeDeltaSeconds, 1),
                        'speed_kmh' => round($speedKmh, 2),
                    ];

                    if ($speedKmh > $maxSpeedKmh) {
                        $maxSpeedKmh = $speedKmh;
                    }
                }

                // Inizia un nuovo pezzo dal punto corrente
                $chunkStartIndex = $i;
                $chunkDistance = 0;
            }
        }

        // Il pezzo residuo (meno della dimensione richiesta) non viene controllato
        // perché il documento dice di dividere in pezzi di N metri, e un pezzo
        // più corto non è significativo per il controllo velocità

        return [
            'max_speed_kmh' => round($maxSpeedKmh, 2),
            'chunks' => $chunks,
        ];
    }

    /**
     * Genera una polyline semplificata (ogni N punti)
     */
    protected function generateSimplifiedPolyline(array $points, int $step = 10): array
    {
        $polyline = [];

        for ($i = 0; $i < count($points); $i += $step) {
            $polyline[] = [
                $points[$i]['latitude'],
                $points[$i]['longitude'],
            ];
        }

        // Assicura che l'ultimo punto sia incluso
        $lastPoint = end($points);
        $lastPolylinePoint = end($polyline);

        if ($lastPolylinePoint[0] !== $lastPoint['latitude'] ||
            $lastPolylinePoint[1] !== $lastPoint['longitude']) {
            $polyline[] = [
                $lastPoint['latitude'],
                $lastPoint['longitude'],
            ];
        }

        return $polyline;
    }

    /**
     * Calcola la distanza tra due punti usando la formula di Vincenty
     * Più accurata della formula di Haversine per brevi distanze
     *
     * @param float $lat1 Latitudine punto 1
     * @param float $lon1 Longitudine punto 1
     * @param float $lat2 Latitudine punto 2
     * @param float $lon2 Longitudine punto 2
     * @return float Distanza in metri
     */
    public function vincentyDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Se i punti sono identici, distanza = 0
        if ($lat1 === $lat2 && $lon1 === $lon2) {
            return 0.0;
        }

        $a = 6378137.0; // Semiasse maggiore WGS-84
        $f = 1 / 298.257223563; // Appiattimento WGS-84
        $b = (1 - $f) * $a;

        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $L = deg2rad($lon2 - $lon1);

        $U1 = atan((1 - $f) * tan($phi1));
        $U2 = atan((1 - $f) * tan($phi2));

        $sinU1 = sin($U1);
        $cosU1 = cos($U1);
        $sinU2 = sin($U2);
        $cosU2 = cos($U2);

        $lambda = $L;
        $lambdaP = 2 * M_PI;
        $iterLimit = 100;

        $sinSigma = 0;
        $cosSigma = 0;
        $sigma = 0;
        $sinAlpha = 0;
        $cosSqAlpha = 0;
        $cos2SigmaM = 0;
        $C = 0;

        while (abs($lambda - $lambdaP) > 1e-12 && --$iterLimit > 0) {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);

            $sinSigma = sqrt(
                pow($cosU2 * $sinLambda, 2) +
                pow($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda, 2)
            );

            if ($sinSigma == 0) {
                return 0.0; // Punti coincidenti
            }

            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cosSqAlpha = 1 - $sinAlpha * $sinAlpha;
            $cos2SigmaM = ($cosSqAlpha != 0) ? $cosSigma - 2 * $sinU1 * $sinU2 / $cosSqAlpha : 0;
            $C = $f / 16 * $cosSqAlpha * (4 + $f * (4 - 3 * $cosSqAlpha));
            $lambdaP = $lambda;
            $lambda = $L + (1 - $C) * $f * $sinAlpha * (
                $sigma + $C * $sinSigma * (
                    $cos2SigmaM + $C * $cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM)
                )
            );
        }

        if ($iterLimit == 0) {
            // Non converge, usa Haversine come fallback
            return $this->haversineDistance($lat1, $lon1, $lat2, $lon2);
        }

        $uSq = $cosSqAlpha * ($a * $a - $b * $b) / ($b * $b);
        $A = 1 + $uSq / 16384 * (4096 + $uSq * (-768 + $uSq * (320 - 175 * $uSq)));
        $B = $uSq / 1024 * (256 + $uSq * (-128 + $uSq * (74 - 47 * $uSq)));
        $deltaSigma = $B * $sinSigma * (
            $cos2SigmaM + $B / 4 * (
                $cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM) -
                $B / 6 * $cos2SigmaM * (-3 + 4 * $sinSigma * $sinSigma) * (-3 + 4 * $cos2SigmaM * $cos2SigmaM)
            )
        );

        return $b * $A * ($sigma - $deltaSigma);
    }

    /**
     * Formula di Haversine (fallback)
     */
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371000; // Raggio medio della Terra in metri

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    /**
     * Filtra punti con scarsa accuratezza GPS
     */
    public function filterByAccuracy(array $points, float $maxAccuracy = 50.0): array
    {
        return array_values(array_filter(
            $points,
            fn($point) => $point['accuracy'] <= $maxAccuracy
        ));
    }

    /**
     * Filtra punti con velocità assoluta superiore al limite (da Algoritmo.pdf: 150 m/s)
     */
    public function filterByMaxAbsoluteSpeed(array $points, float $maxSpeedMs = 150.0): array
    {
        return array_values(array_filter(
            $points,
            fn($point) => $point['speed'] <= $maxSpeedMs
        ));
    }

    /**
     * Filtra punti con velocità anomale per una data modalità
     */
    public function filterBySpeed(array $points, TransportMode $mode): array
    {
        $maxSpeed = $mode->maxSpeedKmh();

        if ($maxSpeed === null) {
            return $points;
        }

        $maxSpeedMs = $maxSpeed / 3.6;

        return array_values(array_filter(
            $points,
            fn($point) => $point['speed'] <= $maxSpeedMs
        ));
    }
}
