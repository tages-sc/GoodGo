<?php

namespace App\Livewire\User\Tracks;

use App\Enums\TrackStatus;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Track $track;

    public function mount(Track $track): void
    {
        $this->track = $track->load(['competition', 'segments']);

        // Verifica che la traccia appartenga all'utente corrente
        if ($this->track->user_id !== Auth::id()) {
            abort(403, 'Non hai accesso a questa traccia.');
        }
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

    public function render()
    {
        return view('livewire.user.tracks.show', [
            'mapData' => $this->getMapData(),
        ]);
    }
}
