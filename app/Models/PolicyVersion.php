<?php

namespace App\Models;

use App\Enums\PolicyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PolicyVersion extends Model
{
    protected $fillable = [
        'type',
        'version',
        'title',
        'content',
        'status',
        'published_at',
        'created_by',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => PolicyType::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * Utente che ha creato la versione
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utente che ha pubblicato la versione
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Scope per policy pubblicate
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope per bozze
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope per tipo
     */
    public function scopeOfType($query, PolicyType $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * Verifica se è una bozza
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verifica se è pubblicata
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Pubblica la versione (irreversibile)
     */
    public function publish(User $user): bool
    {
        if ($this->isPublished()) {
            return false;
        }

        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        // Reset flag accettazione per tutti gli utenti per questo tipo di policy
        if ($this->type === PolicyType::PRIVACY) {
            DB::table('users')
                ->whereNotNull('privacy_accepted_at')
                ->update([
                    'privacy_accepted_at' => null,
                    'privacy_version_id' => null,
                ]);
        } elseif ($this->type === PolicyType::TERMS) {
            DB::table('users')
                ->whereNotNull('terms_accepted_at')
                ->update([
                    'terms_accepted_at' => null,
                    'terms_version_id' => null,
                ]);
        }

        return true;
    }

    /**
     * Ottieni l'ultima versione pubblicata per tipo
     */
    public static function getLatestPublished(PolicyType $type): ?self
    {
        return static::ofType($type)
            ->published()
            ->latest('published_at')
            ->first();
    }

    /**
     * Ottieni l'ultima Privacy Policy pubblicata
     */
    public static function getLatestPrivacyPolicy(): ?self
    {
        return static::getLatestPublished(PolicyType::PRIVACY);
    }

    /**
     * Ottieni gli ultimi Terms pubblicati
     */
    public static function getLatestTerms(): ?self
    {
        return static::getLatestPublished(PolicyType::TERMS);
    }
}
