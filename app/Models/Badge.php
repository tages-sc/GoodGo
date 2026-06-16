<?php

namespace App\Models;

use App\Enums\BadgeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'stars',
        'icon',
        'threshold_type',
        'threshold_value',
        'extra_conditions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => BadgeCategory::class,
            'stars' => 'integer',
            'threshold_value' => 'decimal:2',
            'extra_conditions' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ==================== RELAZIONI ====================

    /**
     * Utenti che hanno vinto questo badge
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, BadgeCategory $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('stars');
    }

    // ==================== METODI HELPER ====================

    /**
     * Numero di utenti che hanno vinto il badge
     */
    public function winnersCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Verifica se un utente ha già vinto il badge
     */
    public function isEarnedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Label con stelle
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->stars > 0) {
            $starSymbol = str_repeat('★', $this->stars);
            return "{$this->name} {$starSymbol}";
        }

        return $this->name;
    }
}
