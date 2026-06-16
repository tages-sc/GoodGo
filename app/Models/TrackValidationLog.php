<?php

namespace App\Models;

use App\Enums\TrackStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackValidationLog extends Model
{
    protected $fillable = [
        'track_id',
        'previous_status',
        'new_status',
        'reason',
        'credits_delta',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_status' => TrackStatus::class,
            'new_status' => TrackStatus::class,
            'credits_delta' => 'decimal:4',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
