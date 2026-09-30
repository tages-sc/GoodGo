<?php

namespace App\Actions\Fortify;

use App\Enums\UserType;
use App\Models\InvitationCode;
use App\Models\PolicyVersion;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\BadgeService;
use App\Services\RegistrationThrottle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if ($seconds = RegistrationThrottle::availableIn(request()->ip())) {
            throw ValidationException::withMessages([
                'email' => RegistrationThrottle::message($seconds),
            ]);
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            'invitation_code' => ['nullable', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ])->validate();

        $user = DB::transaction(function () use ($input) {
            // Ottieni le ultime versioni pubblicate di privacy e terms
            $latestPrivacy = PolicyVersion::getLatestPrivacyPolicy();
            $latestTerms = PolicyVersion::getLatestTerms();

            $user = User::create([
                'name' => $input['name'],
                'surname' => $input['surname'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'type' => UserType::USER,
                'platform' => 'web',
                'credits' => 0,
                'privacy_accepted_at' => $latestPrivacy ? now() : null,
                'privacy_version_id' => $latestPrivacy?->id,
                'terms_accepted_at' => $latestTerms ? now() : null,
                'terms_version_id' => $latestTerms?->id,
            ]);

            // Crea profilo utente vuoto
            UserProfile::create([
                'user_id' => $user->id,
            ]);

            // Iscrivi automaticamente all'ente GoodGo di default
            $user->joinDefaultEnte();

            // Applica codice invito se fornito
            if (!empty($input['invitation_code'])) {
                InvitationCode::applyCode($user, $input['invitation_code']);
            }

            return $user;
        });

        RegistrationThrottle::hit(request()->ip());

        // Assegna badge "Nuovo Utente" (fuori dalla transazione per evitare problemi con notifiche)
        try {
            app(BadgeService::class)->awardRegistrationBadge($user);
        } catch (\Throwable $e) {
            Log::warning('Badge registrazione non assegnato: ' . $e->getMessage());
        }

        return $user;
    }
}
