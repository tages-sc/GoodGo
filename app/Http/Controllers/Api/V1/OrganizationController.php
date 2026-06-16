<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CompetitionStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends ApiController
{
    /**
     * GET api/v1/organizations/
     *
     * Lista enti con filtri.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->input('type');

        if (!$type || !in_array($type, ['mine', 'all'])) {
            return $this->error(100, 'type is required (mine or all)', 422);
        }

        $search = $request->input('search');

        if ($type === 'mine') {
            // Enti a cui l'utente è iscritto
            $query = $user->approvedEnti()->with('enteProfile');
        } else {
            // Tutti gli enti
            $query = User::where('type', UserType::ENTE)->with('enteProfile');
        }

        // Filtro ricerca
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('enteProfile', fn($q2) => $q2->where('location', 'like', "%{$search}%"));
            });
        }

        $enti = $query->get();

        $result = $enti->map(function ($ente) {
            return $this->formatEnteListItem($ente);
        });

        return $this->success($result->values());
    }

    /**
     * GET api/v1/organization/{id}
     *
     * Dettaglio singolo ente.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $ente = User::where('type', UserType::ENTE)
            ->where('id', $id)
            ->with('enteProfile')
            ->first();

        if (!$ente) {
            return $this->error(404, 'Organization not found', 404);
        }

        $profile = $ente->enteProfile;

        $membersCount = $ente->approvedSubscribers()->count();
        $competitionsCount = $ente->organizedCompetitions()
            ->whereIn('status', [CompetitionStatus::PUBLISHED, CompetitionStatus::ACTIVE, CompetitionStatus::ENDED])
            ->count();

        $social = [];
        if ($profile) {
            if ($profile->facebook_url) $social['facebook'] = $profile->facebook_url;
            if ($profile->twitter_url) $social['twitter'] = $profile->twitter_url;
            if ($profile->instagram_url) $social['instagram'] = $profile->instagram_url;
            if ($profile->linkedin_url) $social['linkedin'] = $profile->linkedin_url;
        }

        return $this->success([
            'id' => $ente->id,
            'image' => $profile?->logo ? asset('storage/' . $profile->logo) : '',
            'name' => $ente->name ?? '',
            'type' => $profile?->tipologia ?? '',
            'location' => $profile?->location ?? '',
            'color' => $profile?->colore ?? '',
            'description' => $profile?->descrizione ?? '',
            'website' => $profile?->website ?? '',
            'social' => $social,
            'members' => $membersCount,
            'competitions' => $competitionsCount,
            'moderated' => $profile?->iscrizione_moderata ?? false,
        ]);
    }

    /**
     * POST api/v1/organization/{id}
     *
     * Iscrizione a un ente.
     */
    public function subscribe(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ente = User::where('type', UserType::ENTE)
            ->where('id', $id)
            ->with('enteProfile')
            ->first();

        if (!$ente) {
            return $this->error(404, 'Organization not found', 404);
        }

        // Verifica se già iscritto
        if ($user->enti()->where('ente_id', $id)->exists()) {
            return $this->error(111, 'Already subscribed to this organization', 409);
        }

        // Stato: pending se moderata, approved altrimenti
        $isModerated = $ente->enteProfile?->iscrizione_moderata ?? false;
        $status = $isModerated ? 'pending' : 'approved';

        $user->enti()->attach($id, [
            'status' => $status,
            'requested_at' => now(),
            'processed_at' => $status === 'approved' ? now() : null,
        ]);

        return $this->ok();
    }

    /**
     * PUT api/v1/current-organization/{id}
     *
     * Imposta l'ente corrente.
     */
    public function setCurrent(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ente = User::where('type', UserType::ENTE)
            ->where('id', $id)
            ->first();

        if (!$ente) {
            return $this->error(404, 'Organization not found', 404);
        }

        // Verifica che l'utente sia iscritto all'ente
        if (!$user->approvedEnti()->where('ente_id', $id)->exists()) {
            return $this->error(112, 'Not subscribed to this organization', 403);
        }

        $user->update(['current_ente_id' => $id]);

        return $this->ok();
    }

    /**
     * Formatta un elemento della lista enti.
     */
    private function formatEnteListItem(User $ente): array
    {
        $profile = $ente->enteProfile;

        $membersCount = $ente->approvedSubscribers()->count();
        $competitionsCount = $ente->organizedCompetitions()
            ->whereIn('status', [CompetitionStatus::PUBLISHED, CompetitionStatus::ACTIVE, CompetitionStatus::ENDED])
            ->count();

        return [
            'id' => $ente->id,
            'image' => $profile?->logo ? asset('storage/' . $profile->logo) : '',
            'name' => $ente->name ?? '',
            'type' => $profile?->tipologia ?? '',
            'location' => $profile?->location ?? '',
            'color' => $profile?->colore ?? '',
            'members' => $membersCount,
            'competitions' => $competitionsCount,
            'moderated' => $profile?->iscrizione_moderata ?? false,
        ];
    }
}
