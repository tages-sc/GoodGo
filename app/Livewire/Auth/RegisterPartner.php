<?php

namespace App\Livewire\Auth;

use App\Enums\UserType;
use App\Models\PartnerProfile;
use App\Models\PolicyVersion;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class RegisterPartner extends Component
{
    // Account
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    // Dati minimi aziendali
    public string $company_name = '';
    public string $vat_number = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'accepted',
            'company_name' => 'required|string|max:255',
            'vat_number' => 'required|string|max:20',
        ];
    }

    protected $messages = [
        'name.required' => 'Il nome è obbligatorio.',
        'email.required' => 'L\'email è obbligatoria.',
        'email.email' => 'Inserisci un indirizzo email valido.',
        'email.unique' => 'Questa email è già registrata.',
        'password.required' => 'La password è obbligatoria.',
        'password.min' => 'La password deve avere almeno 8 caratteri.',
        'password.confirmed' => 'Le password non coincidono.',
        'terms.accepted' => 'Devi accettare i termini e le condizioni.',
        'company_name.required' => 'Il nome azienda è obbligatorio.',
        'vat_number.required' => 'La partita IVA è obbligatoria.',
    ];

    public function register(): void
    {
        $this->validate();

        DB::transaction(function () {
            $latestPrivacy = PolicyVersion::getLatestPrivacyPolicy();
            $latestTerms = PolicyVersion::getLatestTerms();

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'type' => UserType::PARTNER,
                'platform' => 'web',
                'credits' => 0,
                'privacy_accepted_at' => $latestPrivacy ? now() : null,
                'privacy_version_id' => $latestPrivacy?->id,
                'terms_accepted_at' => $latestTerms ? now() : null,
                'terms_version_id' => $latestTerms?->id,
            ]);

            // Crea profilo partner con dati minimi - il resto va completato al primo accesso
            PartnerProfile::create([
                'user_id' => $user->id,
                'company_name' => $this->company_name,
                'vat_number' => $this->vat_number,
                'is_verified' => false,
            ]);

            $user->joinDefaultEnte();

            event(new Registered($user));

            Auth::login($user);
        });

        // Redirect al profilo per completare i dati
        $this->redirect(route('profile.show'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register-partner')
            ->layout('layouts.guest');
    }
}
