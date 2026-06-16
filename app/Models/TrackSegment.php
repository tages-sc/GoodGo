<?php

namespace App\Models;

use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_id',
        'sequence',
        'transport_mode',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'distance_meters',
        'points_count',
        'avg_speed_ms',
        'max_speed_ms',
        'max_chunk_speed_kmh',
        'speed_chunks',
        'avg_accuracy_meters',
        'credits_earned',
        'generates_credits',
        'co2_saved_grams',
        'calories_burned',
        'so2_saved_mg',
        'nox_saved_grams',
        'co_saved_grams',
        'pm10_saved_grams',
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
        'rejection_reason',
        'validation_details',
        'polyline',
    ];

    protected $casts = [
        'transport_mode' => TransportMode::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'generates_credits' => 'boolean',
        'validation_details' => 'array',
        'speed_chunks' => 'array',
        'polyline' => 'array',
        'max_chunk_speed_kmh' => 'decimal:2',
        'credits_earned' => 'decimal:4',
        'co2_saved_grams' => 'decimal:2',
        'calories_burned' => 'decimal:2',
        'avg_speed_ms' => 'decimal:4',
        'max_speed_ms' => 'decimal:4',
        'avg_accuracy_meters' => 'decimal:2',
    ];

    // Relationships

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    // Accessors

    public function getDistanceKmAttribute(): float
    {
        return $this->distance_meters / 1000;
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

    public function getMaxSpeedKmhAttribute(): ?float
    {
        return $this->max_speed_ms ? $this->max_speed_ms * 3.6 : null;
    }

    // Methods

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    public function isInvalid(): bool
    {
        return $this->status === 'invalid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Marca il segmento come valido
     */
    public function markAsValid(): void
    {
        $this->update([
            'status' => 'valid',
            'rejection_reason' => null,
            'generates_credits' => $this->transport_mode->generatesCredits(),
        ]);
    }

    /**
     * Marca il segmento come non valido
     */
    public function markAsInvalid(string $reason): void
    {
        $this->update([
            'status' => 'invalid',
            'rejection_reason' => $reason,
            'generates_credits' => false,
        ]);
    }

    /**
     * Calcola i crediti per questo segmento basandosi sulla distanza e modalità
     */
    public function calculateCredits(float $creditsPerKm): float
    {
        if (!$this->generates_credits || !$this->transport_mode->generatesCredits()) {
            return 0;
        }

        return ($this->distance_meters / 1000) * $creditsPerKm;
    }

    /**
     * Calcola le emissioni CO2 risparmiate
     */
    public function calculateCo2Saved(): float
    {
        // CO2 risparmiata = emissioni auto - emissioni modalità usata
        $carEmissionsPerKm = TransportMode::CAR->co2PerKm();
        $modeEmissionsPerKm = $this->transport_mode->co2PerKm();

        $distanceKm = $this->distance_meters / 1000;

        return ($carEmissionsPerKm - $modeEmissionsPerKm) * $distanceKm;
    }

    /**
     * Calcola le calorie bruciate
     */
    public function calculateCalories(): float
    {
        $distanceKm = $this->distance_meters / 1000;

        return $this->transport_mode->caloriesPerKm() * $distanceKm;
    }
}
