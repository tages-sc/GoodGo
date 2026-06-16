<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserType;
use App\Models\InvitationCode;
use App\Models\PolicyVersion;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    /**
     * POST api/v1/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error(100, $validator->errors()->first(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error(101, 'Invalid credentials', 401);
        }

        // Solo utenti generici possono accedere via API
        if ($user->type !== UserType::USER) {
            return $this->error(102, 'Access not allowed for this user type', 403);
        }

        // Crea token Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $this->formatUserData($user),
            'token' => $token,
        ]);
    }

    /**
     * POST api/v1/signup
     */
    public function signup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'privacy_accepted' => ['required', 'accepted'],
            'terms_accepted' => ['required', 'accepted'],
            'invitation_code' => ['nullable', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'platform' => ['nullable', 'string', 'in:ios,android'],
        ]);

        if ($validator->fails()) {
            return $this->error(100, $validator->errors()->first(), 422);
        }

        $user = DB::transaction(function () use ($request) {
            $latestPrivacy = PolicyVersion::getLatestPrivacyPolicy();
            $latestTerms = PolicyVersion::getLatestTerms();

            $user = User::create([
                'name' => $request->first_name,
                'surname' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => UserType::USER,
                'platform' => $request->input('platform', 'android'),
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
            if (!empty($request->invitation_code)) {
                InvitationCode::applyCode($user, $request->invitation_code);
            }

            return $user;
        });

        // Invia email di verifica
        $user->sendEmailVerificationNotification();

        // Assegna badge "Nuovo Utente"
        try {
            app(BadgeService::class)->awardRegistrationBadge($user);
        } catch (\Throwable $e) {
            Log::warning('Badge registrazione non assegnato: ' . $e->getMessage());
        }

        return $this->ok();
    }

    /**
     * Formatta i dati utente per la risposta API.
     */
    public function formatUserData(User $user): array
    {
        $user->load(['profile.occupation', 'currentEnte']);

        $profile = $user->profile;

        return [
            'image' => $user->profile_photo_url,
            'first_name' => $user->name ?? '',
            'last_name' => $user->surname ?? '',
            'email' => $user->email,
            'extra_fields' => [
                'birth_date' => $profile?->birth_date?->format('Y-m-d') ?? '',
                'main_location' => [
                    'lat' => $this->extractLocationField($profile, 'main_location', 'lat'),
                    'lng' => $this->extractLocationField($profile, 'main_location', 'lng'),
                    'address' => $this->extractLocationField($profile, 'main_location', 'address'),
                ],
                'second_location' => [
                    'lat' => $this->extractLocationField($profile, 'second_location', 'lat'),
                    'lng' => $this->extractLocationField($profile, 'second_location', 'lng'),
                    'address' => $this->extractLocationField($profile, 'second_location', 'address'),
                ],
                'occupation' => $profile?->occupation?->name ?? '',
            ],
            'terms' => [
                'privacy' => $user->privacy_accepted_at !== null,
                'terms' => $user->terms_accepted_at !== null,
            ],
            'tutorial' => $user->tutorial ?? false,
            'current_organization_id' => $user->current_ente_id,
        ];
    }

    /**
     * Estrae un campo location dagli extra_fields del profilo.
     */
    private function extractLocationField(?UserProfile $profile, string $location, string $field): mixed
    {
        $extraFields = $profile?->extra_fields ?? [];
        $locationData = $extraFields[$location] ?? [];

        $default = $field === 'address' ? '' : 0;

        return $locationData[$field] ?? $default;
    }
}
