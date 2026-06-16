<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainStation extends Model
{
    protected $fillable = [
        'name',
        'code',
        'latitude',
        'longitude',
        'municipality_id',
        'osm_id',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Trova stazioni entro una certa distanza da un punto (in metri)
     * Usa formula approssimata per piccole distanze
     */
    public function scopeNearby($query, float $lat, float $lng, int $radiusMeters = 100)
    {
        // Approssimazione: 1 grado lat ≈ 111km, 1 grado lng ≈ 111km * cos(lat)
        $latDelta = $radiusMeters / 111000;
        $lngDelta = $radiusMeters / (111000 * cos(deg2rad($lat)));

        return $query->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);
    }
}
