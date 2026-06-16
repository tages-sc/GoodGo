<?php

namespace App\Livewire\Admin\Tracks;

use App\Enums\TrackStatus;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\TrackValidationLog;
use App\Services\TrackValidationManager;
use App\Services\TrackValidationService;
use Livewire\Component;

class Show extends Component
{
    public Track $track;

    public bool $showValidateModal = false;
    public bool $showSegmentModal = false;
    public bool $showTestModal = false;
    public ?int $editingSegmentId = null;

    public string $validationAction = 'valid';
    public string $rejectionReason = '';

    public string $segmentAction = 'valid';
    public string $segmentRejectionReason = '';

    // Risultato del test di validazione
    public ?array $testResult = null;
    public bool $testLoading = false;

    public function mount(Track $track): void
    {
        $this->track = $track->load(['user', 'competition', 'validator', 'segments']);
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

        // Ricalcola metriche traccia
        $this->track->recalculateFromSegments();
        $this->track->refresh();

        $this->showSegmentModal = false;
        $this->editingSegmentId = null;
    }

    public function quickValidateSegment(int $segmentId): void
    {
        $segment = TrackSegment::findOrFail($segmentId);
        $segment->markAsValid();
        $this->track->recalculateFromSegments();
        $this->track->refresh();
        session()->flash('message', 'Segmento validato.');
    }

    public function setProcessing(): void
    {
        $this->track->markAsProcessing();
        $this->track->refresh();
        session()->flash('message', 'Traccia impostata come in elaborazione.');
    }

    /**
     * Apre il modal per il test di validazione
     */
    public function openTestModal(): void
    {
        $this->testResult = null;
        $this->testLoading = false;
        $this->showTestModal = true;
    }

    /**
     * Esegue il test di validazione (dry run)
     */
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

    /**
     * Chiude il modal del test
     */
    public function closeTestModal(): void
    {
        $this->showTestModal = false;
        $this->testResult = null;
    }

    /**
     * Genera i dati per la mappa Leaflet
     */
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

        // Colori per modalità di trasporto
        $colors = [
            'walk' => '#22c55e',      // green
            'bike' => '#3b82f6',      // blue
            'train' => '#f59e0b',     // amber
            'bus' => '#8b5cf6',       // violet
            'car' => '#ef4444',       // red
            'motorcycle' => '#f97316', // orange
        ];

        $mapSegments = [];
        $allPoints = [];

        foreach ($segments as $segment) {
            $polyline = $segment->polyline ?? [];

            if (empty($polyline)) {
                // Se non c'è polyline, usa solo inizio e fine
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

        // Calcola il centro della mappa
        $lats = array_column($allPoints, 0);
        $lngs = array_column($allPoints, 1);

        $center = [
            array_sum($lats) / count($lats),
            array_sum($lngs) / count($lngs),
        ];

        // Calcola bounds per il fit
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

    public function render()
    {
        return view('livewire.admin.tracks.show', [
            'mapData' => $this->getMapData(),
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
