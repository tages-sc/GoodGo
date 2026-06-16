<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    protected $fillable = [
        'province_id',
        'name',
        'postal_code',
        'istat_code',
        'cadastral_code',
        'latitude',
        'longitude',
        'osm_id',
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

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function trainStations(): HasMany
    {
        return $this->hasMany(TrainStation::class);
    }

    public function busStops(): HasMany
    {
        return $this->hasMany(BusStop::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ottieni la regione tramite la provincia
     */
    public function getRegionAttribute()
    {
        return $this->province?->region;
    }
}
