<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationCode extends Model
{
    protected $fillable = [
        'ente_id',
        'name',
        'code',
        'expires_at',
        'is_active',
        'max_uses',
        'uses_count',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
    ];

    public function ente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ente_id');
    }

    /**
     * Scope: solo codici attivi e non scaduti (e con utilizzi disponibili)
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where('expires_at', '>=', today())
            ->where(function ($q) {
                $q->where('max_uses', 0) // 0 = illimitato
                    ->orWhereColumn('uses_count', '<', 'max_uses');
            });
    }

    public function scopeForEnte($query, int $enteId)
    {
        return $query->where('ente_id', $enteId);
    }

    /**
     * Verifica se il codice è ancora valido
     */
    public function isValid(): bool
    {
        return $this->is_active
            && $this->expires_at->gte(today())
            && ($this->max_uses === 0 || $this->uses_count < $this->max_uses);
    }

    /**
     * Genera un codice unico di 6 cifre
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Trova un codice valido dato il suo codice
     */
    public static function findValidByCode(string $code): ?self
    {
        return static::valid()->where('code', $code)->first();
    }

    /**
     * Applica il codice invito: iscrive l'utente all'ente del codice.
     * Ritorna true se l'operazione ha successo.
     */
    public static function applyCode(User $user, string $code): bool
    {
        $invitationCode = static::findValidByCode($code);

        if (!$invitationCode) {
            return false;
        }

        // Controlla se l'utente è già iscritto a questo ente
        if ($user->enti()->where('ente_id', $invitationCode->ente_id)->exists()) {
            return false;
        }

        // Iscrivi l'utente all'ente con stato approvato
        $user->enti()->attach($invitationCode->ente_id, [
            'status' => 'approved',
            'requested_at' => now(),
            'processed_at' => now(),
            'invitation_code_id' => $invitationCode->id,
        ]);

        // Incrementa il contatore utilizzi
        $invitationCode->increment('uses_count');

        return true;
    }
}
