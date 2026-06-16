<?php

namespace App\Models;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionType;
use App\Enums\ExtensionType;
use App\Enums\RewardMode;
use App\Enums\ScoringType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Competition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'rules',
        'rules_document',
        'extra_document',
        'prizes',
        'image',
        'banner',
        'ente_id',
        'organizer_id',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'status',
        'is_public',
        'max_participants',
        'moderated_subscription',
        'extension_type',
        'competition_type',
        'reward_mode',
        'questionnaire_url',
        'age_range',
        'scoring_type',
        'allowed_municipality_ids',
        'allowed_province_ids',
        'allowed_region_ids',
        'min_track_distance',
        'max_track_distance',
        'max_distance_per_mode',
        'max_daily_tracks',
        'max_daily_distance_per_mode',
        'max_earning_per_person',
        'allowed_transport_modes',
        'credits_per_km',
        'credits_multiplier',
        'credits_to_euro',
        'credits_per_mode',
        'leaderboard_type',
        'request_more_data',
        'participants_count',
        'tracks_count',
        'total_distance_km',
        'total_co2_saved_kg',
        'cloned_from_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_start' => 'datetime',
            'registration_end' => 'datetime',
            'status' => CompetitionStatus::class,
            'extension_type' => ExtensionType::class,
            'competition_type' => CompetitionType::class,
            'reward_mode' => RewardMode::class,
            'scoring_type' => ScoringType::class,
            'is_public' => 'boolean',
            'moderated_subscription' => 'boolean',
            'request_more_data' => 'boolean',
            'allowed_municipality_ids' => 'array',
            'allowed_province_ids' => 'array',
            'allowed_region_ids' => 'array',
            'allowed_transport_modes' => 'array',
            'age_range' => 'array',
            'credits_per_mode' => 'array',
            'max_distance_per_mode' => 'array',
            'max_daily_distance_per_mode' => 'array',
            'credits_per_km' => 'decimal:4',
            'credits_multiplier' => 'decimal:2',
            'credits_to_euro' => 'decimal:2',
            'max_earning_per_person' => 'decimal:2',
            'total_distance_km' => 'decimal:2',
            'total_co2_saved_kg' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($competition) {
            if (empty($competition->slug)) {
                $competition->slug = Str::slug($competition->name);
            }
        });
    }

    // ==================== RELAZIONI ====================

    /**
     * Ente organizzatore (utente con type=ente)
     */
    public function ente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ente_id');
    }

    /**
     * Utente organizzatore (opzionale, delegato dall'ente)
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Gara da cui è stata clonata
     */
    public function clonedFrom(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'cloned_from_id');
    }

    /**
     * Gare clonate da questa
     */
    public function clones(): HasMany
    {
        return $this->hasMany(Competition::class, 'cloned_from_id');
    }

    /**
     * Utenti iscritti
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'status',
                'registered_at',
                'approved_at',
                'withdrawn_at',
                'approved_by',
                'rejection_reason',
                'extra_fields',
                'tracks_count',
                'total_distance_km',
                'total_credits',
                'total_co2_saved_kg',
                'rank',
            ])
            ->withTimestamps();
    }

    /**
     * Utenti con iscrizione approvata
     */
    public function approvedUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('status', 'approved');
    }

    /**
     * Utenti in attesa di approvazione
     */
    public function pendingUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('status', 'pending');
    }

    /**
     * Partner commerciali iscritti
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'competition_partner')
            ->withPivot([
                'status',
                'registered_at',
                'approved_at',
                'approved_by',
                'rejection_reason',
                'sponsorship_type',
                'sponsorship_amount',
                'notes',
                'show_in_list',
                'display_order',
            ])
            ->withTimestamps();
    }

    /**
     * Partner approvati
     */
    public function approvedPartners(): BelongsToMany
    {
        return $this->partners()
            ->wherePivot('status', 'approved')
            ->wherePivot('show_in_list', true)
            ->orderByPivot('display_order');
    }

    // ==================== SCOPES ====================

    /**
     * Gare pubbliche
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Gare attive (in corso)
     */
    public function scopeActive($query)
    {
        return $query->where('status', CompetitionStatus::ACTIVE);
    }

    /**
     * Gare pubblicate (visibili ma non ancora iniziate)
     */
    public function scopePublished($query)
    {
        return $query->where('status', CompetitionStatus::PUBLISHED);
    }

    /**
     * Gare a cui è possibile iscriversi
     */
    public function scopeSubscribable($query)
    {
        return $query->whereIn('status', CompetitionStatus::subscribableStatuses());
    }

    /**
     * Gare di un ente specifico
     */
    public function scopeForEnte($query, $enteId)
    {
        return $query->where('ente_id', $enteId);
    }

    /**
     * Gare in corso in una data specifica
     */
    public function scopeRunningOn($query, $date = null)
    {
        $date = $date ?? now()->toDateString();

        return $query
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', CompetitionStatus::ACTIVE);
    }

    /**
     * Gare con modalita premi basati su crediti (dove i partner possono iscriversi)
     */
    public function scopeCreditsBased($query)
    {
        return $query->where('reward_mode', RewardMode::CREDITS_BASED);
    }

    /**
     * Gare disponibili per iscrizione partner (future/in corso + credits_based + pubbliche)
     */
    public function scopeAvailableForPartners($query)
    {
        return $query
            ->where('reward_mode', RewardMode::CREDITS_BASED)
            ->where('is_public', true)
            ->whereIn('status', [CompetitionStatus::PUBLISHED, CompetitionStatus::ACTIVE])
            ->where('end_date', '>=', now()->toDateString());
    }

    // ==================== METODI HELPER ====================

    /**
     * Verifica se la gara è attiva
     */
    public function isActive(): bool
    {
        return $this->status === CompetitionStatus::ACTIVE;
    }

    /**
     * Verifica se la gara è in corso
     */
    public function isRunning(): bool
    {
        return $this->isActive()
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    /**
     * Verifica se le iscrizioni sono aperte
     */
    public function isRegistrationOpen(): bool
    {
        if (!in_array($this->status, CompetitionStatus::subscribableStatuses())) {
            return false;
        }

        if ($this->registration_start && $this->registration_start > now()) {
            return false;
        }

        if ($this->registration_end && $this->registration_end < now()) {
            return false;
        }

        if ($this->max_participants && $this->participants_count >= $this->max_participants) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se l'upload tracce è abilitato
     */
    public function canUploadTracks(): bool
    {
        return $this->isRunning();
    }

    /**
     * Verifica se un utente è iscritto
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Verifica se un utente è iscritto e approvato
     */
    public function hasApprovedUser(User $user): bool
    {
        return $this->approvedUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Verifica se un partner e iscritto alla gara
     */
    public function hasPartner(User $partner): bool
    {
        return $this->partners()->where('user_id', $partner->id)->exists();
    }

    /**
     * Verifica se un partner e iscritto e approvato
     */
    public function hasApprovedPartner(User $partner): bool
    {
        return $this->approvedPartners()->where('user_id', $partner->id)->exists();
    }

    /**
     * Verifica se la gara permette l'iscrizione di partner
     */
    public function allowsPartners(): bool
    {
        return $this->reward_mode === RewardMode::CREDITS_BASED;
    }

    /**
     * Verifica se un partner puo iscriversi (gara credits_based, pubblica, non terminata)
     */
    public function canPartnerSubscribe(): bool
    {
        if (!$this->allowsPartners()) {
            return false;
        }

        if (!$this->is_public) {
            return false;
        }

        if (!in_array($this->status, [CompetitionStatus::PUBLISHED, CompetitionStatus::ACTIVE])) {
            return false;
        }

        if ($this->end_date < now()->toDateString()) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se un utente rientra nelle fasce di eta ammesse dalla gara.
     * Ritorna true se idoneo, false se non idoneo, null se la gara non ha restrizioni di eta.
     */
    public function isUserAgeEligible(User $user): ?bool
    {
        if (empty($this->age_range)) {
            return null;
        }

        $birthDate = $user->profile?->birth_date;
        if (!$birthDate) {
            return false;
        }

        $age = $birthDate->age;

        foreach ($this->age_range as $range) {
            $eligible = match ($range) {
                '<19' => $age < 19,
                '19-30' => $age >= 19 && $age <= 30,
                '30-65' => $age >= 30 && $age <= 65,
                '65+' => $age >= 65,
                default => false,
            };

            if ($eligible) {
                return true;
            }
        }

        return false;
    }

    /**
     * Colonna primaria di ordinamento in base al leaderboard_type
     */
    public function getLeaderboardOrderColumn(): string
    {
        return match ($this->leaderboard_type) {
            'co2' => 'total_co2_saved_kg',
            'gekoin' => 'total_credits',
            default => 'total_distance_km', // 'km'
        };
    }

    /**
     * Colonna secondaria di ordinamento (tiebreaker)
     */
    public function getLeaderboardSecondaryColumn(): string
    {
        return match ($this->leaderboard_type) {
            'co2' => 'total_distance_km',
            'gekoin' => 'total_distance_km',
            default => 'total_credits', // 'km'
        };
    }

    /**
     * Ottieni la classifica
     */
    public function getLeaderboard(int $limit = 50)
    {
        return $this->approvedUsers()
            ->orderByPivot($this->getLeaderboardOrderColumn(), 'desc')
            ->orderByPivot($this->getLeaderboardSecondaryColumn(), 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clona la gara
     */
    public function clone(array $overrides = []): self
    {
        $clone = $this->replicate([
            'slug',
            'status',
            'participants_count',
            'tracks_count',
            'total_distance_km',
            'total_co2_saved_kg',
            'cloned_from_id',
        ]);

        $clone->fill($overrides);
        $clone->status = CompetitionStatus::DRAFT;
        $clone->cloned_from_id = $this->id;
        $clone->participants_count = 0;
        $clone->tracks_count = 0;
        $clone->total_distance_km = 0;
        $clone->total_co2_saved_kg = 0;
        $clone->save();

        return $clone;
    }

    /**
     * Aggiorna le statistiche cache
     */
    public function updateStatistics(): void
    {
        $stats = \Illuminate\Support\Facades\DB::table('competition_user')
            ->where('competition_id', $this->id)
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(tracks_count), 0) as tracks')
            ->selectRaw('COALESCE(SUM(total_distance_km), 0) as distance')
            ->selectRaw('COALESCE(SUM(total_co2_saved_kg), 0) as co2')
            ->first();

        $this->update([
            'participants_count' => $stats->count ?? 0,
            'tracks_count' => $stats->tracks ?? 0,
            'total_distance_km' => $stats->distance ?? 0,
            'total_co2_saved_kg' => $stats->co2 ?? 0,
        ]);
    }

    /**
     * Ricalcola la classifica
     */
    public function recalculateRanks(): void
    {
        $users = $this->approvedUsers()
            ->orderByPivot($this->getLeaderboardOrderColumn(), 'desc')
            ->orderByPivot($this->getLeaderboardSecondaryColumn(), 'desc')
            ->get();

        $rank = 1;
        foreach ($users as $user) {
            $this->users()->updateExistingPivot($user->id, ['rank' => $rank]);
            $rank++;
        }
    }
}
