<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnteProfile extends Model
{
    protected $fillable = [
        'user_id',
        'tipologia',
        'location',
        'descrizione',
        'logo',
        'banner',
        'iscrizione_moderata',
        'website',
        'instagram_url',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
        'colore',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'iscrizione_moderata' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Utente (Ente/Organizer) a cui appartiene questo profilo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope per trovare il profilo dell'ente default (GoodGo)
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Ottieni l'ente default (GoodGo)
     */
    public static function getDefault(): ?self
    {
        return static::default()->first();
    }
}
