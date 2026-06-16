<?php

namespace App\Models;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    protected $fillable = [
        'user_id',
        'partner_id',
        'competition_id',
        'ente_id',
        'credits_amount',
        'euro_amount',
        'exchange_rate',
        'type',
        'status',
        'description',
        'notes',
        'processed_by',
        'processed_at',
        'rejection_reason',
        'credit_log_id',
    ];

    protected function casts(): array
    {
        return [
            'credits_amount' => 'decimal:4',
            'euro_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'type' => MovementType::class,
            'status' => MovementStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    // ==================== RELAZIONI ====================

    /**
     * Utente che ha richiesto il movimento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Partner commerciale (per spese)
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    /**
     * Gara di riferimento
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Ente di riferimento
     */
    public function ente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ente_id');
    }

    /**
     * Chi ha processato il movimento
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Log crediti associato (quando approvato)
     */
    public function creditLog(): BelongsTo
    {
        return $this->belongsTo(CreditLog::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', MovementStatus::PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', MovementStatus::APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', MovementStatus::REJECTED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPartner($query, int $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function scopeForCompetition($query, int $competitionId)
    {
        return $query->where('competition_id', $competitionId);
    }

    public function scopeForEnte($query, int $enteId)
    {
        return $query->where('ente_id', $enteId);
    }

    public function scopeOfType($query, MovementType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', MovementType::EXPENSE);
    }

    // ==================== METODI ====================

    /**
     * Verifica se il movimento può essere processato
     */
    public function canBeProcessed(): bool
    {
        return $this->status->canBeProcessed();
    }

    /**
     * Verifica se il movimento è in stato finale
     */
    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    /**
     * Verifica se è in attesa
     */
    public function isPending(): bool
    {
        return $this->status === MovementStatus::PENDING;
    }

    /**
     * Verifica se è approvato
     */
    public function isApproved(): bool
    {
        return $this->status === MovementStatus::APPROVED;
    }

    /**
     * Verifica se è rifiutato
     */
    public function isRejected(): bool
    {
        return $this->status === MovementStatus::REJECTED;
    }

    /**
     * Verifica se è una spesa
     */
    public function isExpense(): bool
    {
        return $this->type === MovementType::EXPENSE;
    }

    // ==================== ACCESSORS ====================

    /**
     * Importo crediti formattato
     */
    public function getFormattedCreditsAttribute(): string
    {
        return number_format($this->credits_amount, 2, ',', '.');
    }

    /**
     * Importo euro formattato
     */
    public function getFormattedEuroAttribute(): string
    {
        return $this->euro_amount
            ? '€ ' . number_format($this->euro_amount, 2, ',', '.')
            : '-';
    }
}
