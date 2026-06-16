<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransportMode;
use App\Models\Competition;
use App\Models\Track;
use App\Services\TrackUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController extends ApiController
{
    public function __construct(
        protected TrackUploadService $uploadService
    ) {}

    /**
     * GET api/v1/tracks
     *
     * Lista tracce dell'utente con filtri e paginazione.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = (int) $request->input('limit', 10);
        $page = max(1, (int) $request->input('page', 1));

        $query = Track::where('user_id', $user->id)
            ->with('segments', 'competition');

        // Filtro per gare
        if ($request->filled('competition_ids')) {
            $ids = array_map('intval', explode(',', $request->input('competition_ids')));
            $query->whereIn('competition_id', $ids);
        }

        // Filtro per enti (tramite gara)
        if ($request->filled('organization_ids')) {
            $orgIds = array_map('intval', explode(',', $request->input('organization_ids')));
            $query->whereHas('competition', fn($q) => $q->whereIn('ente_id', $orgIds));
        }

        // Filtro date
        if ($request->filled('date_start')) {
            $query->where('started_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->where('started_at', '<=', $request->input('date_end') . ' 23:59:59');
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
                $query->whereHas('segments', fn($q) => $q->whereIn('transport_mode', $dbModes));
            }
        }

        $tracks = $query->orderBy('started_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $result = $tracks->map(function ($track) {
            return $this->formatTrackListItem($track);
        });

        return $this->success($result->values());
    }

    /**
     * POST api/v1/track_upload
     *
     * Upload traccia GPS.
     */
    public function upload(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$request->hasFile('file')) {
            return $this->error(100, 'File is required', 422);
        }

        $competitionId = $request->input('competition_id');
        if (!$competitionId) {
            return $this->error(100, 'competition_id is required', 422);
        }

        $competition = Competition::find($competitionId);
        if (!$competition) {
            return $this->error(404, 'Competition not found', 404);
        }

        // Verifica che l'utente sia iscritto e approvato
        if (!$competition->hasApprovedUser($user)) {
            return $this->error(108, 'User is not an approved participant of this competition', 403);
        }

        $file = $request->file('file');
        $name = $request->input('name');
        $notes = $request->input('notes');

        if ($notes !== null && mb_strlen($notes) > 2000) {
            return $this->error(111, 'notes must be max 2000 characters', 422);
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'zip') {
                $track = $this->uploadService->processUpload($file, $user, $competitionId);
            } elseif (in_array($extension, ['txt', 'csv'])) {
                $track = $this->uploadService->processTextFile($file, $user, $competitionId);
            } else {
                return $this->error(109, 'File must be ZIP, TXT or CSV format', 422);
            }

            $updates = [];
            if ($name) {
                $updates['name'] = $name;
            }
            if ($notes !== null && $notes !== '') {
                $updates['notes'] = $notes;
            }
            if (!empty($updates)) {
                $track->update($updates);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->error(110, $e->getMessage(), 422);
        }

        return $this->ok();
    }

    /**
     * GET api/v1/track/{id}
     *
     * Dettaglio singola traccia.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $track = Track::where('user_id', $user->id)
            ->where('id', $id)
            ->with('segments', 'competition')
            ->first();

        if (!$track) {
            return $this->error(404, 'Track not found', 404);
        }

        $modes = $track->segments
            ->pluck('transport_mode')
            ->filter()
            ->unique()
            ->map(fn(TransportMode $mode) => $mode->apiName())
            ->unique()
            ->values()
            ->toArray();

        // URL download file originale
        $trackDataUrl = '';
        if ($track->original_file_path) {
            $trackDataUrl = url('api/v1/track/' . $track->id . '/download');
        }

        // Gare associate
        $competitions = [];
        if ($track->competition) {
            $competitions[] = [
                'id' => $track->competition->id,
                'name' => $track->competition->name,
            ];
        }

        return $this->success([
            'id' => $track->id,
            'name' => $track->name ?? '',
            'notes' => $track->notes ?? '',
            'modes' => $modes,
            'date_start' => $track->started_at?->format('Y-m-d H:i:s') ?? '',
            'date_end' => $track->ended_at?->format('Y-m-d H:i:s') ?? '',
            'status' => $track->status?->value ?? '',
            'invalid_reason' => $track->rejection_reason ?? '',
            'track_data_url' => $trackDataUrl,
            'competitions' => $competitions,
            'co2' => round(($track->co2_saved_grams ?? 0) / 1000, 2),
            'calories' => round($track->calories_burned ?? 0),
            'co' => round($track->co_saved_grams ?? 0, 2),
            'km' => round(($track->total_distance_meters ?? 0) / 1000, 2),
            'nox' => round($track->nox_saved_grams ?? 0, 2),
            'pm10' => round($track->pm10_saved_grams ?? 0, 2),
            'so2' => round($track->so2_saved_mg ?? 0, 2),
        ]);
    }

    /**
     * DELETE api/v1/track/{id}
     *
     * Elimina una traccia dell'utente.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $track = Track::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$track) {
            return $this->error(404, 'Track not found', 404);
        }

        $this->uploadService->deleteTrack($track);

        return $this->ok();
    }

    /**
     * Formatta un elemento della lista tracce.
     */
    private function formatTrackListItem(Track $track): array
    {
        $modes = $track->segments
            ->pluck('transport_mode')
            ->filter()
            ->unique()
            ->map(fn(TransportMode $mode) => $mode->apiName())
            ->unique()
            ->values()
            ->toArray();

        // Loghi degli enti associati
        $organizationLogos = [];
        if ($track->competition && $track->competition->ente) {
            $ente = $track->competition->ente;
            $logo = $ente->enteProfile?->logo;
            if ($logo) {
                $organizationLogos[] = asset('storage/' . $logo);
            }
        }

        return [
            'id' => $track->id,
            'name' => $track->name ?? '',
            'modes' => $modes,
            'date_start' => $track->started_at?->format('Y-m-d H:i:s') ?? '',
            'date_end' => $track->ended_at?->format('Y-m-d H:i:s') ?? '',
            'lat' => $track->start_latitude ?? 0,
            'lng' => $track->start_longitude ?? 0,
            'distance' => round(($track->total_distance_meters ?? 0) / 1000, 2),
            'status' => $track->status?->value ?? '',
            'invalid_reason' => $track->rejection_reason ?? '',
            'organizations_logos' => $organizationLogos,
        ];
    }
}
