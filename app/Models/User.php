<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'email_verified_at',
        'password',
        'type',
        'platform',
        'credits',
        'privacy_accepted_at',
        'privacy_version_id',
        'terms_accepted_at',
        'terms_version_id',
        'current_ente_id',
        'tutorial',
        'parent_ente_id',
        'deletion_requested_at',
    ];

    /**
     * Nome completo (nome + cognome)
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->surname}");
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
            'credits' => 'decimal:2',
            'privacy_accepted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'tutorial' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ==================== RELAZIONI PROFILO ====================

    /**
     * Profilo utente esteso (per utenti generici)
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Profilo partner (solo per utenti partner)
     */
    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

    /**
     * Profilo ente (solo per utenti ente/organizer)
     */
    public function enteProfile(): HasOne
    {
        return $this->hasOne(EnteProfile::class);
    }

    // ==================== VERIFICHE TIPO UTENTE ====================

    /**
     * Verifica se l'utente è Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->type === UserType::SUPER_ADMIN;
    }

    /**
     * Verifica se l'utente è un Ente
     */
    public function isEnte(): bool
    {
        return $this->type === UserType::ENTE;
    }

    /**
     * Verifica se l'utente è un Organizzatore
     */
    public function isOrganizer(): bool
    {
        return $this->type === UserType::ORGANIZER;
    }

    /**
     * Verifica se l'utente è un Partner
     */
    public function isPartner(): bool
    {
        return $this->type === UserType::PARTNER;
    }

    /**
     * Verifica se l'utente è un Utente generico
     */
    public function isUser(): bool
    {
        return $this->type === UserType::USER;
    }

    /**
     * Verifica se l'utente può gestire gare (ente o organizer)
     */
    public function canManageCompetitions(): bool
    {
        return $this->isEnte() || $this->isOrganizer();
    }

    /**
     * Verifica se l'utente ha accettato privacy e terms
     */
    public function hasAcceptedPolicies(): bool
    {
        return $this->privacy_accepted_at !== null && $this->terms_accepted_at !== null;
    }

    /**
     * Elenco etichette dei campi profilo obbligatori ancora da compilare
     * in base al tipo utente. Vuoto = profilo completo.
     */
    public function missingProfileFields(): array
    {
        return match ($this->type) {
            UserType::PARTNER => $this->missingPartnerProfileFields(),
            UserType::ENTE, UserType::ORGANIZER => $this->missingEnteProfileFields(),
            default => [],
        };
    }

    public function isProfileComplete(): bool
    {
        return $this->missingProfileFields() === [];
    }

    /**
     * Campi profilo partner richiesti (doc 12.2 Dati del profilo, Utente Partner).
     * Esclusi: operational_* (sede esercizio), operator2_* (secondo operatore).
     */
    protected function missingPartnerProfileFields(): array
    {
        $profile = $this->partnerProfile;

        if (!$profile) {
            return ['Profilo partner non creato'];
        }

        $required = [
            // Dati azienda
            'company_name' => 'Nome azienda',
            'vat_number' => 'Partita IVA',
            'fiscal_code' => 'Codice fiscale azienda',
            'company_address' => 'Indirizzo azienda',
            'company_city' => 'Comune azienda',
            'company_postal_code' => 'CAP azienda',
            'company_email' => 'Email azienda',
            'pec' => 'PEC azienda',
            // Sede legale
            'legal_address' => 'Indirizzo sede legale',
            'legal_city' => 'Comune sede legale',
            'legal_postal_code' => 'CAP sede legale',
            // Legale rappresentante
            'legal_rep_name' => 'Nome legale rappresentante',
            'legal_rep_surname' => 'Cognome legale rappresentante',
            'legal_rep_fiscal_code' => 'Codice fiscale legale rappresentante',
            'legal_rep_birth_place' => 'Luogo di nascita legale rappresentante',
            'legal_rep_birth_date' => 'Data di nascita legale rappresentante',
            'legal_rep_address' => 'Indirizzo legale rappresentante',
            'legal_rep_city' => 'Comune legale rappresentante',
            'legal_rep_postal_code' => 'CAP legale rappresentante',
            // Dati bancari
            'iban' => 'IBAN',
            'bank_name' => 'Nome banca',
            // Operatore 1
            'operator_name' => 'Nome operatore',
            'operator_surname' => 'Cognome operatore',
            'operator_fiscal_code' => 'Codice fiscale operatore',
        ];

        $missing = [];
        foreach ($required as $column => $label) {
            if (empty($profile->{$column})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * Campi profilo ente/organizer richiesti (doc 12.2 Dati del profilo, Utente Ente).
     * Esclusi: iscrizione_moderata (boolean con default), URL social (contestualmente opzionali).
     */
    protected function missingEnteProfileFields(): array
    {
        $profile = $this->enteProfile;

        if (!$profile) {
            return ['Profilo ente non creato'];
        }

        $required = [
            'tipologia' => 'Tipologia',
            'location' => 'Location',
            'descrizione' => 'Descrizione',
            'website' => 'Sito web',
            'colore' => 'Colore',
        ];

        $missing = [];
        foreach ($required as $column => $label) {
            if (empty($profile->{$column})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    // ==================== RELAZIONI ENTI (per utenti generici) ====================

    /**
     * Enti a cui l'utente è iscritto
     */
    public function enti(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ente_user', 'user_id', 'ente_id')
            ->using(EnteUser::class)
            ->withPivot(['status', 'requested_at', 'processed_at', 'processed_by', 'notes', 'invitation_code_id'])
            ->withTimestamps();
    }

    /**
     * Enti con iscrizione approvata
     */
    public function approvedEnti(): BelongsToMany
    {
        return $this->enti()->wherePivot('status', 'approved');
    }

    /**
     * Ente corrente selezionato
     */
    public function currentEnte(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_ente_id');
    }

    /**
     * Iscrive l'utente all'ente GoodGo di default
     */
    public function joinDefaultEnte(): void
    {
        $defaultEnte = static::getDefaultEnte();

        if ($defaultEnte && !$this->enti()->where('ente_id', $defaultEnte->id)->exists()) {
            $this->enti()->attach($defaultEnte->id, [
                'status' => 'approved',
                'requested_at' => now(),
                'processed_at' => now(),
            ]);

            // Imposta come ente corrente se non ne ha uno
            if (!$this->current_ente_id) {
                $this->update(['current_ente_id' => $defaultEnte->id]);
            }
        }
    }

    /**
     * Codici invito creati da questo ente
     */
    public function invitationCodes(): HasMany
    {
        return $this->hasMany(InvitationCode::class, 'ente_id');
    }

    // ==================== RELAZIONI ORGANIZZATORI (per utenti ente) ====================

    /**
     * Ente che ha creato questo organizzatore (solo per type=organizer)
     */
    public function parentEnte(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_ente_id');
    }

    /**
     * Organizzatori creati da questo ente (solo per type=ente)
     */
    public function organizers(): HasMany
    {
        return $this->hasMany(User::class, 'parent_ente_id')
            ->where('type', UserType::ORGANIZER);
    }

    // ==================== RELAZIONI ISCRITTI (per utenti ente/organizer) ====================

    /**
     * Utenti iscritti a questo ente (solo per enti/organizer)
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ente_user', 'ente_id', 'user_id')
            ->using(EnteUser::class)
            ->withPivot(['status', 'requested_at', 'processed_at', 'processed_by', 'notes', 'invitation_code_id'])
            ->withTimestamps();
    }

    /**
     * Utenti approvati iscritti a questo ente
     */
    public function approvedSubscribers(): BelongsToMany
    {
        return $this->subscribers()->wherePivot('status', 'approved');
    }

    /**
     * Utenti in attesa di approvazione
     */
    public function pendingSubscribers(): BelongsToMany
    {
        return $this->subscribers()->wherePivot('status', 'pending');
    }

    /**
     * Gare organizzate da questo ente
     */
    public function organizedCompetitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'ente_id');
    }

    /**
     * Gare assegnate a questo organizzatore (solo per type=organizer)
     */
    public function assignedCompetitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'organizer_id');
    }

    /**
     * Verifica se l'organizzatore ha una gara attiva (non terminata/annullata)
     * Una gara è considerata "in corso" se non è ended o cancelled
     */
    public function hasActiveCompetition(): bool
    {
        return $this->assignedCompetitions()
            ->whereNotIn('status', [
                \App\Enums\CompetitionStatus::ENDED->value,
                \App\Enums\CompetitionStatus::CANCELLED->value,
            ])
            ->exists();
    }

    /**
     * Ottieni la gara attiva dell'organizzatore (se esiste)
     */
    public function getActiveCompetition(): ?Competition
    {
        return $this->assignedCompetitions()
            ->whereNotIn('status', [
                \App\Enums\CompetitionStatus::ENDED->value,
                \App\Enums\CompetitionStatus::CANCELLED->value,
            ])
            ->first();
    }

    // ==================== RELAZIONI GARE (per utenti generici) ====================

    /**
     * Gare a cui l'utente è iscritto
     */
    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class)
            ->withPivot([
                'status',
                'registered_at',
                'approved_at',
                'withdrawn_at',
                'approved_by',
                'rejection_reason',
                'tracks_count',
                'total_distance_km',
                'total_credits',
                'total_co2_saved_kg',
                'rank',
            ])
            ->withTimestamps();
    }

    /**
     * Gare con iscrizione approvata
     */
    public function approvedCompetitions(): BelongsToMany
    {
        return $this->competitions()->wherePivot('status', 'approved');
    }

    // ==================== RELAZIONI PARTNER ====================

    /**
     * Gare a cui partecipa come partner (solo per utenti partner)
     */
    public function partnerCompetitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class, 'competition_partner')
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
     * Gare in cui è partner approvato
     */
    public function approvedPartnerCompetitions(): BelongsToMany
    {
        return $this->partnerCompetitions()->wherePivot('status', 'approved');
    }

// ==================== RELAZIONI TRACCE ====================

    /**
     * Tracce caricate dall'utente
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * Tracce validate dell'utente
     */
    public function validTracks(): HasMany
    {
        return $this->tracks()->valid();
    }

    // ==================== RELAZIONI CREDITI E MOVIMENTI ====================

    /**
     * Log delle modifiche crediti
     */
    public function creditLogs(): HasMany
    {
        return $this->hasMany(CreditLog::class);
    }

    /**
     * Movimenti richiesti dall'utente
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * Movimenti ricevuti come partner
     */
    public function partnerMovements(): HasMany
    {
        return $this->hasMany(Movement::class, 'partner_id');
    }

    /**
     * Movimenti pendenti come partner
     */
    public function pendingPartnerMovements(): HasMany
    {
        return $this->partnerMovements()->pending();
    }

    // ==================== RELAZIONI BADGES ====================

    /**
     * Badge vinti dall'utente
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * Verifica se l'utente ha un badge specifico (per slug)
     */
    public function hasBadge(string $slug): bool
    {
        return $this->badges()->where('slug', $slug)->exists();
    }

    // ==================== SCOPES ====================

    /**
     * Solo utenti di tipo ente
     */
    public function scopeEnti($query)
    {
        return $query->where('type', UserType::ENTE);
    }

    /**
     * Solo utenti di tipo organizer
     */
    public function scopeOrganizers($query)
    {
        return $query->where('type', UserType::ORGANIZER);
    }

    /**
     * Solo utenti di tipo partner
     */
    public function scopePartners($query)
    {
        return $query->where('type', UserType::PARTNER);
    }

    /**
     * Solo utenti generici
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('type', UserType::USER);
    }

    // ==================== METODI STATICI ====================

    /**
     * Ottieni l'ente GoodGo default
     */
    public static function getDefaultEnte(): ?self
    {
        return static::where('type', UserType::ENTE)
            ->whereHas('enteProfile', fn($q) => $q->where('is_default', true))
            ->first();
    }
}
