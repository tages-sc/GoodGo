<?php

namespace App\Livewire\Ente\Tracks;

use App\Enums\UserType;
use App\Models\Competition;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\TrackValidationLog;
use App\Services\TrackValidationManager;
use App\Services\TrackValidationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Track $track;
    public Competition $competition;

    public bool $showValidateModal = false;
    public bool $showSegmentModal = false;
    public bool $showTestModal = false;
    public ?int $editingSegmentId = null;

    public string $validationAction = 'valid';
    public string $rejectionReason = '';

    public string $segmentAction = 'valid';
    public string $segmentRejectionReason = '';

    public ?array $testResult = null;
    public bool $testLoading = false;

    public function mount(Competition $competition, Track $track): void
    {
        $this->competition = $competition;
        $this->track = $track->load(['user', 'competition', 'validator', 'segments']);
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();

        // Verifica che la traccia appartenga alla gara
        if ($this->track->competition_id !== $this->competition->id) {
            abort(404);
        }

        if ($user->type === UserType::ENTE) {
            if ($this->competition->ente_id !== $user->id) {
                abort(403, 'Non hai accesso a questa traccia.');
            }
        } elseif ($user->type === UserType::ORGANIZER) {
            if ($this->competition->organizer_id !== $user->id) {
                abort(403, 'Non hai accesso a questa traccia.');
            }
        } else {
            abort(403, 'Accesso non autorizzato.');
        }
    }

    public function openValidateModal(): void
    {
        $this->validationAction = 'valid';
        $this->rejectionReason = '';
        $this->showValidateModal = true;
    }

    public function validateTrack(TrackValidationManager $validationManager): void
    {
        if ($this->validationAction === 'valid') {
            $creditsAdded = $validationManager->validate($this->track, auth()->id());
            session()->flash('message', 'Traccia validata con successo.' . ($creditsAdded > 0 ? " Assegnati {$creditsAdded} crediti." : ''));
        } else {
            if (empty($this->rejectionReason)) {
                $this->addError('rejectionReason', 'La motivazione è obbligatoria.');
                return;
            }

            $creditsRemoved = $validationManager->invalidate($this->track, $this->rejectionReason, auth()->id());
            session()->flash('message', 'Traccia invalidata.' . ($creditsRemoved > 0 ? " Rimossi {$creditsRemoved} crediti." : ''));
        }

        $this->showValidateModal = false;
        $this->track->refresh();
    }

    public function openSegmentModal(int $segmentId): void
    {
        $this->editingSegmentId = $segmentId;
        $segment = TrackSegment::find($segmentId);
        $this->segmentAction = $segment->status === 'valid' ? 'valid' : ($segment->status === 'invalid' ? 'invalid' : 'valid');
        $this->segmentRejectionReason = $segment->rejection_reason ?? '';
        $this->showSegmentModal = true;
    }

    public function validateSegment(): void
    {
        if (!$this->editingSegmentId) {
            return;
        }

        $segment = TrackSegment::findOrFail($this->editingSegmentId);

        // Verifica che il segmento appartenga alla traccia
        if ($segment->track_id !== $this->track->id) {
            abort(403);
        }

        if ($this->segmentAction === 'valid') {
            $segment->markAsValid();
            session()->flash('message', 'Segmento validato.');
        } else {
            if (empty($this->segmentRejectionReason)) {
                $this->addError('segmentRejectionReason', 'La motivazione è obbligatoria.');
                return;
            }
            $segment->markAsInvalid($this->segmentRejectionReason);
            session()->flash('message', 'Segmento invalidato.');
        }

        $this->track->recalculateFromSegments();
        $this->track->refresh();

        $this->showSegmentModal = false;
        $this->editingSegmentId = null;
    }

    public function quickValidateSegment(int $segmentId): void
    {
        $segment = TrackSegment::findOrFail($segmentId);

        if ($segment->track_id !== $this->track->id) {
            abort(403);
        }

        $segment->markAsValid();
        $this->track->recalculateFromSegments();
        $this->track->refresh();
        session()->flash('message', 'Segmento validato.');
    }

    public function openTestModal(): void
    {
        $this->testResult = null;
        $this->testLoading = false;
        $this->showTestModal = true;
    }

    public function runValidationTest(): void
    {
        $this->testLoading = true;

        try {
            $validationService = app(TrackValidationService::class);
            $this->testResult = $validationService->dryRun($this->track);
        } catch (\Exception $e) {
            $this->testResult = [
                'is_valid' => false,
                'dry_run' => true,
                'rejection_reason' => 'Errore durante il test: ' . $e->getMessage(),
                'warnings' => [],
                'segments_details' => [],
            ];
        }

        $this->testLoading = false;
    }

    public function closeTestModal(): void
    {
        $this->showTestModal = false;
        $this->testResult = null;
    }

    public function getMapData(): array
    {
        $segments = $this->track->segments;

        if ($segments->isEmpty()) {
            return [
                'center' => [
                    $this->track->start_latitude ?? 43.7,
                    $this->track->start_longitude ?? 10.4,
                ],
                'zoom' => 13,
                'segments' => [],
            ];
        }

        $colors = [
            'walk' => '#22c55e',
            'bike' => '#3b82f6',
            'train' => '#f59e0b',
            'bus' => '#8b5cf6',
            'car' => '#ef4444',
            'motorcycle' => '#f97316',
        ];

        $mapSegments = [];
        $allPoints = [];

        foreach ($segments as $segment) {
            $polyline = $segment->polyline ?? [];

            if (empty($polyline)) {
                $polyline = [
                    [$segment->start_latitude, $segment->start_longitude],
                    [$segment->end_latitude, $segment->end_longitude],
                ];
            }

            $allPoints = array_merge($allPoints, $polyline);

            $mapSegments[] = [
                'id' => $segment->id,
                'sequence' => $segment->sequence,
                'transport_mode' => $segment->transport_mode->value,
                'transport_mode_label' => $segment->transport_mode->label(),
                'color' => $colors[$segment->transport_mode->value] ?? '#6b7280',
                'status' => $segment->status,
                'distance_km' => round($segment->distance_km, 2),
                'duration' => $segment->duration_formatted,
                'avg_speed_kmh' => $segment->avg_speed_kmh ? round($segment->avg_speed_kmh, 1) : null,
                'polyline' => $polyline,
                'generates_credits' => $segment->generates_credits,
            ];
        }

        $lats = array_column($allPoints, 0);
        $lngs = array_column($allPoints, 1);

        $center = [
            array_sum($lats) / count($lats),
            array_sum($lngs) / count($lngs),
        ];

        $bounds = [
            'north' => max($lats),
            'south' => min($lats),
            'east' => max($lngs),
            'west' => min($lngs),
        ];

        return [
            'center' => $center,
            'bounds' => $bounds,
            'zoom' => 14,
            'segments' => $mapSegments,
            'start' => [
                (float) $this->track->start_latitude,
                (float) $this->track->start_longitude,
            ],
            'end' => [
                (float) $this->track->end_latitude,
                (float) $this->track->end_longitude,
            ],
        ];
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->track->status) {
            TrackStatus::PENDING => 'bg-yellow-100 text-yellow-800',
            TrackStatus::PROCESSING => 'bg-blue-100 text-blue-800',
            TrackStatus::VALID => 'bg-green-100 text-green-800',
            TrackStatus::INVALID => 'bg-red-100 text-red-800',
            TrackStatus::NOT_CREDITED => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Determina il nome della rotta per tornare alla lista tracce
     */
    public function getBackRouteName(): string
    {
        $user = Auth::user();

        if ($user->type === UserType::ENTE) {
            return 'ente.competitions.tracks';
        }

        return 'organizer.competitions.tracks';
    }

    public function render()
    {
        return view('livewire.ente.tracks.show', [
            'mapData' => $this->getMapData(),
            'backRouteName' => $this->getBackRouteName(),
            'validationLogs' => $this->getValidationLogs(),
        ]);
    }

    /**
     * Storico transizioni di stato (valide/invalide) per questa traccia.
     */
    protected function getValidationLogs()
    {
        return TrackValidationLog::with('changedBy')
            ->where('track_id', $this->track->id)
            ->orderByDesc('created_at')
            ->get();
    }
}
