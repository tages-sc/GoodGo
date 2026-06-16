<?php

namespace App\Livewire\Admin\Users;

use App\Enums\CreditLogType;
use App\Enums\UserType;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public User $user;

    // Dati base utente (tutti i tipi)
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $type = '';
    public ?string $platform = null;
    public bool $email_verified = false;

    // Sub-form gestione crediti (modifica transazionale con log)
    public string $creditsAmount = '';
    public string $creditsDescription = '';
    public string $creditsOperation = 'add';

    // Profilo Utente Generico (USER)
    public ?string $username = null;
    public ?string $birth_date = null;
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $city = null;
    public ?string $province = null;
    public ?string $postal_code = null;

    // Profilo Partner (PARTNER)
    public ?string $company_name = null;
    public ?string $vat_number = null;
    public ?string $fiscal_code = null;
    public ?string $company_type = null;
    public ?string $legal_address = null;
    public ?string $legal_city = null;
    public ?string $legal_province = null;
    public ?string $legal_postal_code = null;
    public ?string $operational_address = null;
    public ?string $operational_city = null;
    public ?string $operational_province = null;
    public ?string $operational_postal_code = null;
    public ?string $company_phone = null;
    public ?string $company_email = null;
    public ?string $pec = null;
    public ?string $partner_website = null;
    public ?string $legal_rep_name = null;
    public ?string $legal_rep_surname = null;
    public ?string $legal_rep_fiscal_code = null;
    public ?string $legal_rep_phone = null;
    public ?string $legal_rep_email = null;
    public ?string $iban = null;
    public ?string $bank_name = null;
    public ?string $swift_bic = null;
    public ?string $operator_name = null;
    public ?string $operator_surname = null;
    public ?string $operator_phone = null;
    public ?string $operator_email = null;
    public ?string $partner_description = null;
    public $partner_logo = null;
    public bool $is_verified = false;
    public ?string $nfc_code = null;
    public ?string $partner_latitude = null;
    public ?string $partner_longitude = null;

    // Profilo Ente/Organizer (ENTE, ORGANIZER)
    public ?string $tipologia = null;
    public ?string $location = null;
    public ?string $ente_descrizione = null;
    public ?string $ente_website = null;
    public ?string $instagram_url = null;
    public ?string $linkedin_url = null;
    public ?string $twitter_url = null;
    public ?string $facebook_url = null;
    public ?string $colore = null;
    public bool $iscrizione_moderata = false;
    public $ente_logo = null;
    public $ente_banner = null;

    // Per organizer
    public ?int $parent_ente_id = null;

    public function mount(User $user): void
    {
        $this->user = $user->load(['profile', 'partnerProfile', 'enteProfile']);

        // Dati base
        $this->name = $user->name;
        $this->email = $user->email;
        $this->type = $user->type->value;
        $this->platform = $user->platform;
        $this->email_verified = $user->email_verified_at !== null;
        $this->parent_ente_id = $user->parent_ente_id;

        // Carica dati profilo in base al tipo
        $this->loadProfileData();
    }

    protected function loadProfileData(): void
    {
        switch ($this->user->type) {
            case UserType::USER:
                if ($this->user->profile) {
                    $this->username = $this->user->profile->username;
                    $this->birth_date = $this->user->profile->birth_date?->format('Y-m-d');
                    $this->phone = $this->user->profile->phone;
                    $this->address = $this->user->profile->address;
                    $this->city = $this->user->profile->city;
                    $this->province = $this->user->profile->province;
                    $this->postal_code = $this->user->profile->postal_code;
                }
                break;

            case UserType::PARTNER:
                if ($this->user->partnerProfile) {
                    $profile = $this->user->partnerProfile;
                    $this->company_name = $profile->company_name;
                    $this->vat_number = $profile->vat_number;
                    $this->fiscal_code = $profile->fiscal_code;
                    $this->company_type = $profile->company_type;
                    $this->legal_address = $profile->legal_address;
                    $this->legal_city = $profile->legal_city;
                    $this->legal_province = $profile->legal_province;
                    $this->legal_postal_code = $profile->legal_postal_code;
                    $this->operational_address = $profile->operational_address;
                    $this->operational_city = $profile->operational_city;
                    $this->operational_province = $profile->operational_province;
                    $this->operational_postal_code = $profile->operational_postal_code;
                    $this->company_phone = $profile->company_phone;
                    $this->company_email = $profile->company_email;
                    $this->pec = $profile->pec;
                    $this->partner_website = $profile->website;
                    $this->legal_rep_name = $profile->legal_rep_name;
                    $this->legal_rep_surname = $profile->legal_rep_surname;
                    $this->legal_rep_fiscal_code = $profile->legal_rep_fiscal_code;
                    $this->legal_rep_phone = $profile->legal_rep_phone;
                    $this->legal_rep_email = $profile->legal_rep_email;
                    $this->iban = $profile->iban;
                    $this->bank_name = $profile->bank_name;
                    $this->swift_bic = $profile->swift_bic;
                    $this->operator_name = $profile->operator_name;
                    $this->operator_surname = $profile->operator_surname;
                    $this->operator_phone = $profile->operator_phone;
                    $this->operator_email = $profile->operator_email;
                    $this->partner_description = $profile->description;
                    $this->is_verified = $profile->is_verified ?? false;
                    $this->nfc_code = $profile->nfc_code;
                    $this->partner_latitude = $profile->latitude !== null ? (string) $profile->latitude : null;
                    $this->partner_longitude = $profile->longitude !== null ? (string) $profile->longitude : null;
                }
                break;

            case UserType::ENTE:
            case UserType::ORGANIZER:
                if ($this->user->enteProfile) {
                    $profile = $this->user->enteProfile;
                    $this->tipologia = $profile->tipologia;
                    $this->location = $profile->location;
                    $this->ente_descrizione = $profile->descrizione;
                    $this->ente_website = $profile->website;
                    $this->instagram_url = $profile->instagram_url;
                    $this->linkedin_url = $profile->linkedin_url;
                    $this->twitter_url = $profile->twitter_url;
                    $this->facebook_url = $profile->facebook_url;
                    $this->colore = $profile->colore;
                    $this->iscrizione_moderata = $profile->iscrizione_moderata ?? false;
                }
                break;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'type' => ['required', Rule::in(UserType::values())],
            'platform' => 'nullable|in:ios,android,web',
            'email_verified' => 'boolean',
        ];

        // Regole specifiche per tipo utente
        switch (UserType::from($this->type)) {
            case UserType::USER:
                $rules += [
                    'username' => 'nullable|string|max:255',
                    'birth_date' => 'nullable|date',
                    'phone' => 'nullable|string|max:50',
                    'address' => 'nullable|string|max:500',
                    'city' => 'nullable|string|max:100',
                    'province' => 'nullable|string|max:2',
                    'postal_code' => 'nullable|string|max:10',
                ];
                break;

            case UserType::PARTNER:
                $rules += [
                    'company_name' => 'required|string|max:255',
                    'vat_number' => 'required|string|max:20',
                    'fiscal_code' => 'nullable|string|max:20',
                    'company_type' => 'nullable|string|max:100',
                    'legal_address' => 'nullable|string|max:255',
                    'legal_city' => 'nullable|string|max:100',
                    'legal_province' => 'nullable|string|max:2',
                    'legal_postal_code' => 'nullable|string|max:10',
                    'operational_address' => 'nullable|string|max:255',
                    'operational_city' => 'nullable|string|max:100',
                    'operational_province' => 'nullable|string|max:2',
                    'operational_postal_code' => 'nullable|string|max:10',
                    'company_phone' => 'nullable|string|max:50',
                    'company_email' => 'nullable|email|max:255',
                    'pec' => 'nullable|email|max:255',
                    'partner_website' => 'nullable|url|max:255',
                    'legal_rep_name' => 'nullable|string|max:100',
                    'legal_rep_surname' => 'nullable|string|max:100',
                    'legal_rep_fiscal_code' => 'nullable|string|max:20',
                    'legal_rep_phone' => 'nullable|string|max:50',
                    'legal_rep_email' => 'nullable|email|max:255',
                    'iban' => 'nullable|string|max:34',
                    'bank_name' => 'nullable|string|max:100',
                    'swift_bic' => 'nullable|string|max:11',
                    'operator_name' => 'nullable|string|max:100',
                    'operator_surname' => 'nullable|string|max:100',
                    'operator_phone' => 'nullable|string|max:50',
                    'operator_email' => 'nullable|email|max:255',
                    'partner_description' => 'nullable|string|max:2000',
                    'partner_logo' => 'nullable|image|max:2048',
                    'is_verified' => 'boolean',
                    'nfc_code' => 'nullable|string|max:50',
                    'partner_latitude' => 'nullable|numeric|between:-90,90',
                    'partner_longitude' => 'nullable|numeric|between:-180,180',
                ];
                break;

            case UserType::ENTE:
            case UserType::ORGANIZER:
                $rules += [
                    'tipologia' => 'nullable|in:comune,azienda',
                    'location' => 'nullable|string|max:255',
                    'ente_descrizione' => 'nullable|string|max:2000',
                    'ente_website' => 'nullable|url|max:255',
                    'instagram_url' => 'nullable|url|max:255',
                    'linkedin_url' => 'nullable|url|max:255',
                    'twitter_url' => 'nullable|url|max:255',
                    'facebook_url' => 'nullable|url|max:255',
                    'colore' => 'nullable|string|max:7',
                    'iscrizione_moderata' => 'boolean',
                    'ente_logo' => 'nullable|image|max:2048',
                    'ente_banner' => 'nullable|image|max:2048',
                ];

                if (UserType::from($this->type) === UserType::ORGANIZER) {
                    $rules['parent_ente_id'] = 'nullable|exists:users,id';
                }
                break;
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        // Aggiorna dati utente base (il saldo crediti si modifica solo via adjustCredits)
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->type,
            'platform' => $this->platform ?: null,
        ];

        // Gestione password
        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        // Gestione email verificata
        if ($this->email_verified && !$this->user->email_verified_at) {
            $userData['email_verified_at'] = now();
        } elseif (!$this->email_verified && $this->user->email_verified_at) {
            $userData['email_verified_at'] = null;
        }

        // Parent ente per organizer
        if (UserType::from($this->type) === UserType::ORGANIZER) {
            $userData['parent_ente_id'] = $this->parent_ente_id;
        } else {
            $userData['parent_ente_id'] = null;
        }

        $this->user->update($userData);

        // Salva profilo specifico per tipo
        $this->saveProfileData();

        session()->flash('message', 'Utente aggiornato con successo.');

        $this->redirect(route('admin.users.show', $this->user), navigate: true);
    }

    /**
     * Aggiunge o sottrae crediti all'utente passando da CreditService
     * (crea sempre una entry in credits_log con balance_before/after).
     */
    public function adjustCredits(CreditService $creditService): void
    {
        $this->validate([
            'creditsAmount' => 'required|numeric|min:0.01',
            'creditsDescription' => 'required|string|max:255',
            'creditsOperation' => 'required|in:add,subtract',
        ]);

        $amount = (float) $this->creditsAmount;
        $admin = auth()->user();

        try {
            if ($this->creditsOperation === 'add') {
                $creditService->addCredits(
                    $this->user,
                    $amount,
                    CreditLogType::MANUAL_ADD,
                    $this->creditsDescription,
                    $admin
                );
                session()->flash('message', "Aggiunti {$amount} crediti.");
            } else {
                $creditService->subtractCredits(
                    $this->user,
                    $amount,
                    CreditLogType::MANUAL_SUBTRACT,
                    $this->creditsDescription,
                    $admin
                );
                session()->flash('message', "Sottratti {$amount} crediti.");
            }
        } catch (\InvalidArgumentException $e) {
            $this->addError('creditsAmount', $e->getMessage());
            return;
        }

        $this->user->refresh();
        $this->resetCreditsForm();
    }

    protected function resetCreditsForm(): void
    {
        $this->creditsAmount = '';
        $this->creditsDescription = '';
        $this->creditsOperation = 'add';
    }

    protected function saveProfileData(): void
    {
        $userType = UserType::from($this->type);

        switch ($userType) {
            case UserType::USER:
                $profileData = [
                    'username' => $this->username ?: null,
                    'birth_date' => $this->birth_date ?: null,
                    'phone' => $this->phone ?: null,
                    'address' => $this->address ?: null,
                    'city' => $this->city ?: null,
                    'province' => $this->province ?: null,
                    'postal_code' => $this->postal_code ?: null,
                ];

                if ($this->user->profile) {
                    $this->user->profile->update($profileData);
                } else {
                    $this->user->profile()->create($profileData);
                }
                break;

            case UserType::PARTNER:
                $profileData = [
                    'company_name' => $this->company_name,
                    'vat_number' => $this->vat_number,
                    'fiscal_code' => $this->fiscal_code ?: null,
                    'company_type' => $this->company_type ?: null,
                    'legal_address' => $this->legal_address ?: null,
                    'legal_city' => $this->legal_city ?: null,
                    'legal_province' => $this->legal_province ?: null,
                    'legal_postal_code' => $this->legal_postal_code ?: null,
                    'operational_address' => $this->operational_address ?: null,
                    'operational_city' => $this->operational_city ?: null,
                    'operational_province' => $this->operational_province ?: null,
                    'operational_postal_code' => $this->operational_postal_code ?: null,
                    'company_phone' => $this->company_phone ?: null,
                    'company_email' => $this->company_email ?: null,
                    'pec' => $this->pec ?: null,
                    'website' => $this->partner_website ?: null,
                    'legal_rep_name' => $this->legal_rep_name ?: null,
                    'legal_rep_surname' => $this->legal_rep_surname ?: null,
                    'legal_rep_fiscal_code' => $this->legal_rep_fiscal_code ?: null,
                    'legal_rep_phone' => $this->legal_rep_phone ?: null,
                    'legal_rep_email' => $this->legal_rep_email ?: null,
                    'iban' => $this->iban ?: null,
                    'bank_name' => $this->bank_name ?: null,
                    'swift_bic' => $this->swift_bic ?: null,
                    'operator_name' => $this->operator_name ?: null,
                    'operator_surname' => $this->operator_surname ?: null,
                    'operator_phone' => $this->operator_phone ?: null,
                    'operator_email' => $this->operator_email ?: null,
                    'description' => $this->partner_description ?: null,
                    'is_verified' => $this->is_verified,
                ];

                // Codice NFC: modificabile solo da Super Admin
                if (auth()->user()?->isSuperAdmin()) {
                    $profileData['nfc_code'] = $this->nfc_code ?: null;
                }

                // Coordinate (override manuale): modificabili solo da Super Admin
                if (auth()->user()?->isSuperAdmin()) {
                    $latProvided = $this->partner_latitude !== null && $this->partner_latitude !== '';
                    $lngProvided = $this->partner_longitude !== null && $this->partner_longitude !== '';
                    $profileData['latitude'] = $latProvided ? (float) $this->partner_latitude : null;
                    $profileData['longitude'] = $lngProvided ? (float) $this->partner_longitude : null;
                    $profileData['geocoded_at'] = ($latProvided && $lngProvided) ? now() : null;
                }

                if ($this->is_verified && (!$this->user->partnerProfile || !$this->user->partnerProfile->verified_at)) {
                    $profileData['verified_at'] = now();
                } elseif (!$this->is_verified) {
                    $profileData['verified_at'] = null;
                }

                if ($this->partner_logo) {
                    $profileData['logo'] = $this->partner_logo->store('partners/logos', 'public');
                }

                if ($this->user->partnerProfile) {
                    $this->user->partnerProfile->update($profileData);
                } else {
                    $this->user->partnerProfile()->create($profileData);
                }
                break;

            case UserType::ENTE:
            case UserType::ORGANIZER:
                $profileData = [
                    'tipologia' => $this->tipologia ?: null,
                    'location' => $this->location ?: null,
                    'descrizione' => $this->ente_descrizione ?: null,
                    'website' => $this->ente_website ?: null,
                    'instagram_url' => $this->instagram_url ?: null,
                    'linkedin_url' => $this->linkedin_url ?: null,
                    'twitter_url' => $this->twitter_url ?: null,
                    'facebook_url' => $this->facebook_url ?: null,
                    'colore' => $this->colore ?: null,
                    'iscrizione_moderata' => $this->iscrizione_moderata,
                ];

                if ($this->ente_logo) {
                    $profileData['logo'] = $this->ente_logo->store('enti/logos', 'public');
                }

                if ($this->ente_banner) {
                    $profileData['banner'] = $this->ente_banner->store('enti/banners', 'public');
                }

                if ($this->user->enteProfile) {
                    $this->user->enteProfile->update($profileData);
                } else {
                    $this->user->enteProfile()->create($profileData);
                }
                break;
        }
    }

    public function render()
    {
        return view('livewire.admin.users.edit', [
            'userTypes' => UserType::cases(),
            'platforms' => ['ios', 'android', 'web'],
            'enti' => User::where('type', UserType::ENTE)->orderBy('name')->get(),
        ]);
    }
}
