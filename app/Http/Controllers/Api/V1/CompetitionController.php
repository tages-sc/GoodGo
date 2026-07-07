<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CompetitionStatus;
use App\Enums\TransportMode;
use App\Models\Competition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CompetitionController extends ApiController
{
    /**
     * GET api/v1/competitions/
     *
     * Lista gare con filtri e paginazione.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->input('status');

        if (!$status || !in_array($status, ['ongoing', 'finished'])) {
            return $this->error(100, 'status is required (ongoing or finished)', 422);
        }

        $limit = in_array($request->input('limit'), [10, 50]) ? (int) $request->input('limit') : 10;
        $page = max(1, (int) $request->input('page', 1));

        $query = Competition::where('is_public', true);

        if ($status === 'ongoing') {
            $query->whereIn('status', [CompetitionStatus::PUBLISHED, CompetitionStatus::ACTIVE]);
        } else {
            $query->where('status', CompetitionStatus::ENDED);
        }

        // Filtro per ente
        if ($request->filled('ente')) {
            $query->where('ente_id', $request->input('ente'));
        }

        $competitions = $query->orderBy('start_date', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $result = $competitions->map(function ($competition) use ($user) {
            return $this->formatCompetitionListItem($competition, $user);
        });

        return $this->success($result->values());
    }

    /**
     * GET api/v1/competition/{id}
     *
     * Dettaglio singola gara.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $competition = Competition::find($id);

        if (!$competition) {
            return $this->error(404, 'Competition not found', 404);
        }

        // Tipo gara
        $type = $competition->reward_mode?->value === 'ranking' ? 'Classifica' : 'Incrementale';

        // Modalità consentite
        $modes = collect($competition->allowed_transport_modes ?? [])
            ->map(fn($mode) => TransportMode::tryFrom($mode)?->apiName())
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Aggiungi "multi" se multimodale (più di 1 modalità)
        if (count($modes) > 1) {
            $modes[] = 'multi';
        }

        // Leaderboard
        $leaderboard = $this->formatLeaderboard($competition, $user);

        // Documenti
        $documents = [];
        if ($competition->rules_document) {
            $documents[] = [
                'name' => 'Regolamento',
                'url' => asset('storage/' . $competition->rules_document),
            ];
        }
        if ($competition->extra_document) {
            $documents[] = [
                'name' => 'Documento aggiuntivo',
                'url' => asset('storage/' . $competition->extra_document),
            ];
        }

        return $this->success([
            'id' => $competition->id,
            'image' => $competition->image ? asset('storage/' . $competition->image) : '',
            'title' => $competition->name,
            'description' => $competition->description ?? '',
            'type' => $type,
            'rules' => $competition->rules ?? '',
            'modes' => $modes,
            'date_start' => $competition->start_date?->format('Y-m-d') ?? '',
            'date_end' => $competition->end_date?->format('Y-m-d') ?? '',
            'organization_id' => $competition->ente_id,
            'leaderboard_type' => $competition->leaderboard_type ?? 'km',
            'request_more_data' => $competition->request_more_data ?? false,
            'documents' => $documents,
            'leaderboard' => $leaderboard,
            'results' => [],
        ]);
    }

    /**
     * GET api/v1/competition-abstract/{id}
     *
     * Abstract pubblico di una gara: dati minimi visibili anche senza
     * autenticazione (protetto dalla sola secret-key). Espone unicamente
     * gare pubbliche e non in bozza (published/active/ended); qualsiasi
     * altra gara (bozza, cancellata, privata) ritorna 404.
     */
    public function publicAbstract(int $id): JsonResponse
    {
        $competition = Competition::where('is_public', true)
            ->whereIn('status', [
                CompetitionStatus::PUBLISHED,
                CompetitionStatus::ACTIVE,
                CompetitionStatus::ENDED,
            ])
            ->find($id);

        if (!$competition) {
            return $this->error(404, 'Competition not found', 404);
        }

        // Modalità premi (coerente col campo 'type' del dettaglio gara)
        $rewardMode = $competition->reward_mode?->value === 'ranking' ? 'Classifica' : 'Incrementale';

        // Documenti allegati (regolamento + eventuale documento aggiuntivo)
        $documents = [];
        if ($competition->rules_document) {
            $documents[] = [
                'name' => 'Regolamento',
                'url' => asset('storage/' . $competition->rules_document),
            ];
        }
        if ($competition->extra_document) {
            $documents[] = [
                'name' => 'Documento aggiuntivo',
                'url' => asset('storage/' . $competition->extra_document),
            ];
        }

        return $this->success([
            'id' => $competition->id,
            'title' => $competition->name,
            'type' => $rewardMode,
            'extension_type' => $competition->competition_type?->label() ?? '',
            'image' => $competition->image ? asset('storage/' . $competition->image) : '',
            'banner' => $competition->banner ? asset('storage/' . $competition->banner) : '',
            'date_start' => $competition->start_date?->format('Y-m-d') ?? '',
            'date_end' => $competition->end_date?->format('Y-m-d') ?? '',
            'registration_start' => $competition->registration_start?->format('Y-m-d H:i:s') ?? '',
            'registration_end' => $competition->registration_end?->format('Y-m-d H:i:s') ?? '',
            'documents' => $documents,
        ]);
    }

    /**
     * POST api/v1/competition/subscribe
     *
     * Iscrizione a una gara.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();
        $competitionId = $request->input('id');

        if (!$competitionId) {
            return $this->error(100, 'Competition id is required', 422);
        }

        $competition = Competition::find($competitionId);
        if (!$competition) {
            return $this->error(404, 'Competition not found', 404);
        }

        if (!$competition->isRegistrationOpen()) {
            return $this->error(103, 'Registration is not open for this competition', 403);
        }

        if ($competition->hasUser($user)) {
            return $this->error(104, 'Already subscribed to this competition', 409);
        }

        // Verifica età se necessario
        $ageEligible = $competition->isUserAgeEligible($user);
        if ($ageEligible === false) {
            return $this->error(105, 'User age is not eligible for this competition', 403);
        }

        // Stato iscrizione: pending se moderata, approved altrimenti
        $status = $competition->moderated_subscription ? 'pending' : 'approved';

        $pivotData = [
            'status' => $status,
            'registered_at' => now(),
        ];

        if ($status === 'approved') {
            $pivotData['approved_at'] = now();
        }

        // Salva extra_fields se forniti
        $extraFields = $request->input('extra_fields');
        if (!empty($extraFields)) {
            $pivotData['extra_fields'] = json_encode($extraFields);
        }

        $user->competitions()->attach($competitionId, $pivotData);

        // Aggiorna contatore partecipanti se approvato
        if ($status === 'approved') {
            $competition->increment('participants_count');
        }

        return $this->ok();
    }

    /**
     * POST api/v1/competition/unsubscribe/{id}
     *
     * Disiscrizione da una gara.
     */
    public function unsubscribe(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $competition = Competition::find($id);

        if (!$competition) {
            return $this->error(404, 'Competition not found', 404);
        }

        if (!$competition->hasUser($user)) {
            return $this->error(106, 'Not subscribed to this competition', 404);
        }

        // Verifica che la gara non sia terminata
        if ($competition->status === CompetitionStatus::ENDED) {
            return $this->error(107, 'Cannot unsubscribe from a finished competition', 403);
        }

        $wasApproved = $competition->hasApprovedUser($user);

        $user->competitions()->detach($id);

        if ($wasApproved) {
            $competition->decrement('participants_count');
        }

        return $this->ok();
    }

    /**
     * GET api/v1/competition/{id}/partners
     *
     * Lista dei partner approvati di una gara con codice NFC e dati visibili.
     */
    public function partners(Request $request, int $id): JsonResponse
    {
        $competition = Competition::find($id);

        if (!$competition) {
            return $this->error(404, 'Competition not found', 404);
        }

        $partners = $competition->approvedPartners()
            ->with('partnerProfile')
            ->get();

        $result = $partners->map(function ($partner) {
            $profile = $partner->partnerProfile;

            return [
                'id' => $partner->id,
                'name' => $profile?->company_name ?? $partner->name,
                'nfc_code' => $profile?->nfc_code,
                'logo' => $profile?->logo ? asset('storage/' . $profile->logo) : '',
                'description' => $profile?->description ?? '',
                'address' => $profile?->company_address ?? '',
                'city' => $profile?->company_city ?? '',
                'province' => $profile?->company_province ?? '',
                'postal_code' => $profile?->company_postal_code ?? '',
                'latitude' => $profile?->latitude !== null ? (float) $profile->latitude : null,
                'longitude' => $profile?->longitude !== null ? (float) $profile->longitude : null,
                'phone' => $profile?->company_phone ?? '',
                'email' => $profile?->company_email ?? '',
                'website' => $profile?->website ?? '',
                'sponsorship_type' => $partner->pivot->sponsorship_type,
                'display_order' => (int) $partner->pivot->display_order,
            ];
        });

        return $this->success($result->values());
    }

    /**
     * Formatta un elemento della lista gare.
     */
    private function formatCompetitionListItem(Competition $competition, $user): array
    {
        $modes = collect($competition->allowed_transport_modes ?? [])
            ->map(fn($mode) => TransportMode::tryFrom($mode)?->apiName())
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (count($modes) > 1) {
            $modes[] = 'multi';
        }

        $type = $competition->reward_mode?->value === 'ranking' ? 'Classifica' : 'Incrementale';

        $leaderboard = $this->formatLeaderboard($competition, $user);

        return [
            'id' => $competition->id,
            'image' => $competition->image ? asset('storage/' . $competition->image) : '',
            'title' => $competition->name,
            'description' => $competition->description ?? '',
            'type' => $type,
            'rules' => $competition->rules ?? '',
            'modes' => $modes,
            'date_start' => $competition->start_date?->format('Y-m-d') ?? '',
            'date_end' => $competition->end_date?->format('Y-m-d') ?? '',
            'organization_id' => $competition->ente_id,
            'leaderboard_type' => $competition->leaderboard_type ?? 'km',
            'leaderboard' => $leaderboard,
        ];
    }

    /**
     * Formatta la leaderboard con nomi offuscati.
     */
    private function formatLeaderboard(Competition $competition, $currentUser): array
    {
        $users = $competition->getLeaderboard(50);
        $orderColumn = $competition->getLeaderboardOrderColumn();

        return $users->map(function ($user) use ($currentUser, $orderColumn) {
            $isMe = $user->id === $currentUser->id;

            // Offusca il nome se non è l'utente corrente
            $name = $isMe
                ? $user->name
                : $this->obfuscateName($user->name);

            $result = [
                'name' => $name,
                'value' => round($user->pivot->{$orderColumn} ?? 0),
                'rank' => $user->pivot->rank ?? 0,
            ];

            if ($isMe) {
                $result['is_me'] = true;
            }

            return $result;
        })->values()->toArray();
    }

    /**
     * Offusca un nome per privacy (es. "Leonardo" -> "L***o").
     */
    private function obfuscateName(string $name): string
    {
        if (strlen($name) <= 2) {
            return $name[0] . '*';
        }

        return $name[0] . str_repeat('*', strlen($name) - 2) . substr($name, -1);
    }
}
