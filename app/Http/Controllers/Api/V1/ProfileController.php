<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CompetitionStatus;
use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Models\Competition;
use App\Models\Occupation;
use App\Models\Track;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProfileController extends ApiController
{
    /**
     * GET api/v1/profile
     *
     * Dati personali dell'utente loggato.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success(app(AuthController::class)->formatUserData($user));
    }

    /**
     * GET api/v1/profile/detail
     *
     * Dati aggiuntivi e statistiche.
     */
    public function detail(Request $request): JsonResponse
    {
        $user = $request->user();

        // Gare in corso e completate
        $competitionsOngoing = $user->approvedCompetitions()
            ->where('competitions.status', CompetitionStatus::ACTIVE)
            ->count();

        $competitionsCompleted = $user->approvedCompetitions()
            ->where('competitions.status', CompetitionStatus::ENDED)
            ->count();

        // Movimenti ultimi 3 mesi (km per modalità per mese)
        $movements = $this->getMovementsLast3Months($user);

        // Statistiche aggregate
        $stats = $this->getAggregatedStats($user);

        // Ultime 3 tracce (movimenti nel doc)
        $lastMovements = $this->getLastTracks($user, 3);

        // Ultime 3 gare approvate
        $lastApprovedCompetitions = $this->getLastApprovedCompetitions($user, 3);

        return $this->success([
            'competitions_ongoing' => $competitionsOngoing,
            'competitions_completed' => $competitionsCompleted,
            'movements' => $movements,
            'stats' => $stats,
            'last_movements' => $lastMovements,
            'last_approved_competitions' => $lastApprovedCompetitions,
        ]);
    }

    /**
     * PUT api/v1/profile
     *
     * Aggiornamento profilo utente.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        // Aggiorna dati utente base
        if ($request->has('first_name')) {
            $user->name = $request->input('first_name');
        }
        if ($request->has('last_name')) {
            $user->surname = $request->input('last_name');
        }
        if ($request->has('tutorial')) {
            $user->tutorial = filter_var($request->input('tutorial'), FILTER_VALIDATE_BOOLEAN);
        }

        // Upload immagine profilo
        if ($request->hasFile('image')) {
            $user->updateProfilePhoto($request->file('image'));
        }

        $user->save();

        // Aggiorna extra_fields nel profilo
        if ($request->has('extra_fields')) {
            $profile = $user->profile ?? UserProfile::create(['user_id' => $user->id]);
            $extraFields = $request->input('extra_fields');

            $currentExtra = $profile->extra_fields ?? [];

            if (isset($extraFields['birth_date'])) {
                $profile->birth_date = $extraFields['birth_date'];
            }

            if (isset($extraFields['main_location'])) {
                $currentExtra['main_location'] = $extraFields['main_location'];
            }

            if (isset($extraFields['second_location'])) {
                $currentExtra['second_location'] = $extraFields['second_location'];
            }

            if (isset($extraFields['occupation'])) {
                // Cerca l'occupazione per nome
                $occupation = Occupation::active()->where('name', $extraFields['occupation'])->first();
                $profile->occupation_id = $occupation?->id;
            }

            $profile->extra_fields = $currentExtra;
            $profile->save();
        }

        return $this->ok();
    }

    /**
     * GET api/v1/profile/competitions
     *
     * Lista gare dell'utente con filtri.
     */
    public function competitions(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = $request->input('organization_id');

        if (!$organizationId) {
            return $this->error(100, 'organization_id is required', 422);
        }

        $query = $user->competitions()
            ->whereHas('ente', fn($q) => $q->where('id', $organizationId));

        // Filtro date
        if ($request->filled('date_start')) {
            $query->where('start_date', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->where('end_date', '<=', $request->input('date_end'));
        }

        // Filtro modalità
        if ($request->filled('modes')) {
            $modes = explode(',', $request->input('modes'));
            $dbModes = [];
            foreach ($modes as $apiMode) {
                $mode = TransportMode::fromApiName(trim($apiMode));
                if ($mode) {
                    $dbModes[] = $mode->value;
                }
            }
            if (!empty($dbModes)) {
                $query->where(function ($q) use ($dbModes) {
                    foreach ($dbModes as $mode) {
                        $q->orWhereJsonContains('allowed_transport_modes', $mode);
                    }
                });
            }
        }

        $competitions = $query->get();

        $result = $competitions->map(function ($competition) use ($user) {
            $pivot = $competition->pivot;

            // Stats per modalità per questa gara
            $stats = $this->getCompetitionUserStats($user->id, $competition->id);

            return [
                'id' => $competition->id,
                'date_registration' => $pivot->registered_at ? Carbon::parse($pivot->registered_at)->format('Y-m-d') : '',
                'registration_status' => $pivot->status,
                'organization_id' => $competition->ente_id,
                'stats' => $stats,
            ];
        });

        return $this->success($result->values());
    }

    /**
     * Km per modalità per mese (ultimi 3 mesi).
     */
    private function getMovementsLast3Months($user): array
    {
        $months = [];
        $now = Carbon::now();

        for ($i = 0; $i < 3; $i++) {
            $date = $now->copy()->subMonths($i);
            $monthName = strtolower($date->translatedFormat('F'));

            $tracks = Track::where('user_id', $user->id)
                ->where('status', TrackStatus::VALID)
                ->whereYear('started_at', $date->year)
                ->whereMonth('started_at', $date->month)
                ->with('segments')
                ->get();

            $kmByMode = ['walk' => 0, 'bicycle' => 0, 'scooter' => 0, 'public_transport' => 0];

            foreach ($tracks as $track) {
                foreach ($track->segments as $segment) {
                    $apiName = TransportMode::from($segment->transport_mode)->apiName();
                    $km = round($segment->distance_meters / 1000, 2);

                    // bicycle e scooter usano lo stesso apiName per bike
                    if ($apiName === 'bicycle') {
                        $kmByMode['bicycle'] += $km;
                    } elseif ($apiName === 'public_transport') {
                        $kmByMode['public_transport'] += $km;
                    } elseif ($apiName === 'walk') {
                        $kmByMode['walk'] += $km;
                    }
                    // scooter è mappato su bicycle nel TransportMode
                }
            }

            $months[$monthName] = [
                'walk' => round($kmByMode['walk']),
                'bicycle' => round($kmByMode['bicycle']),
                'scooter' => 0, // bike/scooter condividono BIKE nel model
                'public_transport' => round($kmByMode['public_transport']),
            ];
        }

        return $months;
    }

    /**
     * Statistiche aggregate dell'utente.
     */
    private function getAggregatedStats($user): array
    {
        $validTracks = Track::where('user_id', $user->id)
            ->where('status', TrackStatus::VALID)
            ->get();

        $totalKm = $validTracks->sum('total_distance_meters') / 1000;

        // Km per modalità
        $segmentStats = DB::table('track_segments')
            ->join('tracks', 'track_segments.track_id', '=', 'tracks.id')
            ->where('tracks.user_id', $user->id)
            ->where('tracks.status', TrackStatus::VALID->value)
            ->select('track_segments.transport_mode', DB::raw('SUM(track_segments.distance_meters) as total_meters'))
            ->groupBy('track_segments.transport_mode')
            ->get()
            ->keyBy('transport_mode');

        $walkKm = round(($segmentStats->get('walk')?->total_meters ?? 0) / 1000);
        $bikeKm = round(($segmentStats->get('bike')?->total_meters ?? 0) / 1000);
        $busKm = round(($segmentStats->get('bus')?->total_meters ?? 0) / 1000);
        $trainKm = round(($segmentStats->get('train')?->total_meters ?? 0) / 1000);
        $tplKm = $busKm + $trainKm;

        return [
            'total_km' => round($totalKm),
            'total_km_by_walk' => $walkKm,
            'total_km_by_bicycle' => $bikeKm,
            'total_km_by_scooter' => 0,
            'total_km_by_public_transport' => $tplKm,
            'total_co2' => round($validTracks->sum('co2_saved_grams') / 1000), // grammi -> kg
            'total_gekoin' => round($validTracks->sum('credits_earned')),
            'total_calories' => round($validTracks->sum('calories_burned')),
            'total_co' => round($validTracks->sum('co_saved_grams')),
            'total_nox' => round($validTracks->sum('nox_saved_grams')),
            'total_pm10' => round($validTracks->sum('pm10_saved_grams')),
            'total_so2' => round($validTracks->sum('so2_saved_mg')),
        ];
    }

    /**
     * Ultime N tracce dell'utente.
     */
    private function getLastTracks($user, int $limit): array
    {
        $tracks = Track::where('user_id', $user->id)
            ->where('status', TrackStatus::VALID)
            ->with('segments', 'competition')
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();

        return $tracks->map(function ($track) {
            $modes = $track->segments
                ->pluck('transport_mode')
                ->unique()
                ->map(fn($mode) => TransportMode::from($mode)->apiName())
                ->unique()
                ->values()
                ->toArray();

            // Enti associati tramite la gara
            $organizationIds = [];
            if ($track->competition) {
                $organizationIds[] = $track->competition->ente_id;
            }

            return [
                'id' => $track->id,
                'name' => $track->name ?? '',
                'modes' => $modes,
                'date_start' => $track->started_at?->format('Y-m-d H:i:s') ?? '',
                'km' => round($track->total_distance_meters / 1000, 1),
                'organization_ids' => $organizationIds,
            ];
        })->values()->toArray();
    }

    /**
     * Ultime N gare approvate dell'utente (ordinate per ultimo movimento/traccia).
     */
    private function getLastApprovedCompetitions($user, int $limit): array
    {
        $competitions = $user->approvedCompetitions()
            ->orderByPivot('registered_at', 'desc')
            ->limit($limit)
            ->get();

        return $competitions->map(function ($competition) use ($user) {
            $pivot = $competition->pivot;
            $stats = $this->getCompetitionUserStats($user->id, $competition->id);

            return [
                'id' => $competition->id,
                'date_registration' => $pivot->registered_at ? Carbon::parse($pivot->registered_at)->format('Y-m-d') : '',
                'registration_status' => $pivot->status,
                'organization_id' => $competition->ente_id,
                'stats' => $stats,
            ];
        })->values()->toArray();
    }

    /**
     * Stats per modalità dell'utente in una gara specifica.
     */
    private function getCompetitionUserStats(int $userId, int $competitionId): array
    {
        $segmentStats = DB::table('track_segments')
            ->join('tracks', 'track_segments.track_id', '=', 'tracks.id')
            ->where('tracks.user_id', $userId)
            ->where('tracks.competition_id', $competitionId)
            ->where('tracks.status', TrackStatus::VALID->value)
            ->select(
                'track_segments.transport_mode',
                DB::raw('SUM(track_segments.distance_meters) / 1000 as km'),
                DB::raw('SUM(track_segments.co2_saved_grams) / 1000 as co2'),
                DB::raw('SUM(track_segments.credits_earned) as gekoin'),
                DB::raw('SUM(track_segments.calories_burned) as calories'),
                DB::raw('SUM(track_segments.co_saved_grams) as co'),
                DB::raw('SUM(track_segments.nox_saved_grams) as nox'),
                DB::raw('SUM(track_segments.pm10_saved_grams) as pm10'),
                DB::raw('SUM(track_segments.so2_saved_mg) as so2')
            )
            ->groupBy('track_segments.transport_mode')
            ->get();

        if ($segmentStats->isEmpty()) {
            return [];
        }

        return $segmentStats->map(function ($stat) {
            $apiName = TransportMode::from($stat->transport_mode)->apiName();

            return [
                'type' => $apiName,
                'km' => round($stat->km),
                'co2' => round($stat->co2),
                'gekoin' => round($stat->gekoin),
                'calories' => round($stat->calories),
                'co' => round($stat->co),
                'nox' => round($stat->nox),
                'pm10' => round($stat->pm10),
                'so2' => round($stat->so2),
            ];
        })->values()->toArray();
    }
}
