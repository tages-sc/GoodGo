<?php

namespace App\Models;

use App\Enums\CreditLogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditLog extends Model
{
    protected $table = 'credits_log';

    protected $fillable = [
        'user_id',
        'amount',
        'balance_before',
        'balance_after',
        'type',
        'description',
        'causer_type',
        'causer_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'balance_before' => 'decimal:4',
            'balance_after' => 'decimal:4',
            'type' => CreditLogType::class,
            'metadata' => 'array',
        ];
    }

    // ==================== RELAZIONI ====================

    /**
     * Utente proprietario del log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Entità che ha causato la modifica (User, Track, Movement, etc.)
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, CreditLogType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeAdditions($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeSubtractions($query)
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==================== ACCESSORS ====================

    /**
     * Importo formattato con segno
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->amount >= 0 ? '+' : '';
        return $sign . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Indica se è un'aggiunta
     */
    public function getIsAdditionAttribute(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Indica se è una sottrazione
     */
    public function getIsSubtractionAttribute(): bool
    {
        return $this->amount < 0;
    }
}
