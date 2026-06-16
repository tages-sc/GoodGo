<?php

namespace App\Models;

use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competition_id',
        'session_id',
        'name',
        'notes',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'total_distance_meters',
        'valid_distance_meters',
        'is_multimodal',
        'primary_transport_mode',
        'credits_earned',
        'credits_per_km_applied',
        'co2_saved_grams',
        'so2_saved_mg',
        'nox_saved_grams',
        'co_saved_grams',
        'pm10_saved_grams',
        'calories_burned',
        'co2_emitted_grams',
        'so2_emitted_mg',
        'nox_emitted_grams',
        'co_emitted_grams',
        'pm10_emitted_grams',
        'cost_fuel_euros',
        'cost_depreciation_euros',
        'cost_operation_euros',
        'cost_time_euros',
        'cost_total_euros',
        'original_file_path',
        'original_file_hash',
        'points_count',
        'avg_accuracy_meters',
        'avg_speed_ms',
        'validated_at',
        'validated_by',
        'rejection_reason',
        'validation_warnings',
        'device_platform',
        'device_info',
    ];

    protected $casts = [
        'status' => TrackStatus::class,
        'primary_transport_mode' => TransportMode::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'validated_at' => 'datetime',
        'is_multimodal' => 'boolean',
        'validation_warnings' => 'array',
        'device_info' => 'array',
        'credits_earned' => 'decimal:4',
        'credits_per_km_applied' => 'decimal:4',
        'co2_saved_grams' => 'decimal:2',
        'calories_burned' => 'decimal:2',
        'avg_accuracy_meters' => 'decimal:2',
        'avg_speed_ms' => 'decimal:4',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(TrackSegment::class)->orderBy('sequence');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', TrackStatus::PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', TrackStatus::PROCESSING);
    }

    public function scopeValid($query)
    {
        return $query->where('status', TrackStatus::VALID);
    }

    public function scopeInvalid($query)
    {
        return $query->where('status', TrackStatus::INVALID);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCompetition($query, int $competitionId)
    {
        return $query->where('competition_id', $competitionId);
    }

    // Accessors

    public function getTotalDistanceKmAttribute(): float
    {
        return $this->total_distance_meters / 1000;
    }

    public function getValidDistanceKmAttribute(): float
    {
        return $this->valid_distance_meters / 1000;
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_seconds) {
            return '-';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getAvgSpeedKmhAttribute(): ?float
    {
        return $this->avg_speed_ms ? $this->avg_speed_ms * 3.6 : null;
    }

    // Methods

    public function isPending(): bool
    {
        return $this->status === TrackStatus::PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === TrackStatus::PROCESSING;
    }

    public function isValid(): bool
    {
        return $this->status === TrackStatus::VALID;
    }

    public function isInvalid(): bool
    {
        return $this->status === TrackStatus::INVALID;
    }

    public function canBeValidated(): bool
    {
        return in_array($this->status, [TrackStatus::PENDING, TrackStatus::PROCESSING]);
    }

    /**
     * Marca la traccia come in elaborazione
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => TrackStatus::PROCESSING]);
    }

    /**
     * Marca la traccia come valida
     */
    public function markAsValid(?int $validatedBy = null): void
    {
        $this->update([
            'status' => TrackStatus::VALID,
            'validated_at' => now(),
            'validated_by' => $validatedBy,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Marca la traccia come non valida
     */
    public function markAsInvalid(string $reason, ?int $validatedBy = null): void
    {
        $this->update([
            'status' => TrackStatus::INVALID,
            'rejection_reason' => $reason,
            'validated_at' => now(),
            'validated_by' => $validatedBy,
        ]);
    }

    /**
     * Calcola il riepilogo dai segmenti
     */
    public function recalculateFromSegments(): void
    {
        $segments = $this->segments;

        if ($segments->isEmpty()) {
            return;
        }

        // Determina lo stato della traccia in base ai segmenti
        $statuses = $segments->pluck('status')->unique();
        $allInvalid = $statuses->count() === 1 && $statuses->first() === 'invalid';
        $allValid = $statuses->count() === 1 && $statuses->first() === 'valid';
        $hasValid = $statuses->contains('valid');
        $hasPending = $statuses->contains('pending');

        $updateData = [
            'total_distance_meters' => $segments->sum('distance_meters'),
            'valid_distance_meters' => $segments->where('status', 'valid')->sum('distance_meters'),
            'credits_earned' => $segments->sum('credits_earned'),
            'co2_saved_grams' => $segments->sum('co2_saved_grams'),
            'so2_saved_mg' => $segments->sum('so2_saved_mg'),
            'nox_saved_grams' => $segments->sum('nox_saved_grams'),
            'co_saved_grams' => $segments->sum('co_saved_grams'),
            'pm10_saved_grams' => $segments->sum('pm10_saved_grams'),
            'calories_burned' => $segments->sum('calories_burned'),
            'co2_emitted_grams' => $segments->sum('co2_emitted_grams'),
            'so2_emitted_mg' => $segments->sum('so2_emitted_mg'),
            'nox_emitted_grams' => $segments->sum('nox_emitted_grams'),
            'co_emitted_grams' => $segments->sum('co_emitted_grams'),
            'pm10_emitted_grams' => $segments->sum('pm10_emitted_grams'),
            'cost_fuel_euros' => $segments->sum('cost_fuel_euros'),
            'cost_depreciation_euros' => $segments->sum('cost_depreciation_euros'),
            'cost_operation_euros' => $segments->sum('cost_operation_euros'),
            'cost_time_euros' => $segments->sum('cost_time_euros'),
            'cost_total_euros' => $segments->sum('cost_total_euros'),
            'points_count' => $segments->sum('points_count'),
            'is_multimodal' => $segments->pluck('transport_mode')->unique()->count() > 1,
        ];

        // Propaga lo stato dei segmenti alla traccia (solo se non ci sono segmenti pending)
        if (!$hasPending) {
            if ($allInvalid) {
                $updateData['status'] = TrackStatus::INVALID;
                $updateData['validated_at'] = now();
            } elseif ($allValid || $hasValid) {
                $updateData['status'] = TrackStatus::VALID;
                $updateData['validated_at'] = now();
            }
        }

        $this->update($updateData);
    }

    /**
     * Determina la modalita di trasporto principale (quella con piu distanza)
     */
    public function determinePrimaryTransportMode(): ?TransportMode
    {
        $segments = $this->segments;

        if ($segments->isEmpty()) {
            return null;
        }

        $distanceByMode = $segments->groupBy('transport_mode')
            ->map(fn($group) => $group->sum('distance_meters'));

        $primaryMode = $distanceByMode->sortDesc()->keys()->first();

        return $primaryMode ? TransportMode::from($primaryMode) : null;
    }
}
