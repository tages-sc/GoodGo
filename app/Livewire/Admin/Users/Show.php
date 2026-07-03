<?php

namespace App\Livewire\Admin\Users;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Enums\UserType;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Show extends Component
{
    use WithPagination;

    public User $user;
    public string $activeTab = 'info';

    protected $queryString = ['activeTab'];

    public function mount(User $user): void
    {
        $this->user = $user->load([
            'profile',
            'partnerProfile',
            'enteProfile',
            'currentEnte',
            'parentEnte',
        ]);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function exportUserData(): StreamedResponse
    {
        $user = $this->user->load([
            'profile',
            'tracks.segments',
            'movements.partner',
            'movements.competition',
            'creditLogs',
            'badges',
            'competitions',
        ]);

        $filename = 'dati_utente_' . $user->id . '_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($user) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // === SEZIONE: PROFILO ===
            fputcsv($handle, ['=== PROFILO UTENTE ==='], ';');
            fputcsv($handle, ['Campo', 'Valore'], ';');
            fputcsv($handle, ['ID', $user->id], ';');
            fputcsv($handle, ['Nome', $user->name], ';');
            fputcsv($handle, ['Email', $user->email], ';');
            fputcsv($handle, ['Tipo', $user->type->label()], ';');
            fputcsv($handle, ['Piattaforma', $user->platform ?? '-'], ';');
            fputcsv($handle, ['Crediti', number_format($user->credits, 2, ',', '.')], ';');
            fputcsv($handle, ['Data Registrazione', local_dt($user->created_at, 'd/m/Y H:i')], ';');
            fputcsv($handle, ['Email Verificata', local_dt($user->email_verified_at, 'd/m/Y H:i') ?: 'No'], ';');

            if ($user->profile) {
                fputcsv($handle, ['Username', $user->profile->username ?? '-'], ';');
                fputcsv($handle, ['Data Nascita', $user->profile->birth_date?->format('d/m/Y') ?? '-'], ';');
                fputcsv($handle, ['Indirizzo', $user->profile->address ?? '-'], ';');
            }
            fputcsv($handle, [], ';');

            // === SEZIONE: GARE ===
            fputcsv($handle, ['=== GARE ==='], ';');
            fputcsv($handle, ['ID Gara', 'Nome', 'Stato Iscrizione', 'Data Iscrizione', 'Crediti Gara'], ';');
            foreach ($user->competitions as $comp) {
                fputcsv($handle, [
                    $comp->id,
                    $comp->name,
                    $comp->pivot->status ?? '-',
                    local_dt($comp->pivot->registered_at, 'd/m/Y H:i') ?: '-',
                    number_format($comp->pivot->total_credits ?? 0, 2, ',', '.'),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // === SEZIONE: TRACCE ===
            fputcsv($handle, ['=== TRACCE ==='], ';');
            fputcsv($handle, ['ID', 'Data', 'Stato', 'Distanza (km)', 'CO2 Risparmiata (kg)', 'Calorie', 'Segmenti'], ';');
            foreach ($user->tracks as $track) {
                fputcsv($handle, [
                    $track->id,
                    local_dt($track->started_at, 'd/m/Y H:i'),
                    $track->status->label(),
                    number_format(($track->total_distance_meters ?? 0) / 1000, 2, ',', '.'),
                    number_format(($track->co2_saved_grams ?? 0) / 1000, 3, ',', '.'),
                    $track->calories_burned ?? 0,
                    $track->segments->count(),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // === SEZIONE: MOVIMENTI ===
            fputcsv($handle, ['=== MOVIMENTI ==='], ';');
            fputcsv($handle, ['ID', 'Tipo', 'Stato', 'Crediti', 'Euro', 'Partner', 'Gara', 'Data'], ';');
            foreach ($user->movements as $movement) {
                fputcsv($handle, [
                    $movement->id,
                    $movement->type->label(),
                    $movement->status->label(),
                    number_format($movement->credits_amount, 2, ',', '.'),
                    number_format($movement->euro_amount ?? 0, 2, ',', '.'),
                    $movement->partner?->name ?? '-',
                    $movement->competition?->name ?? '-',
                    local_dt($movement->created_at, 'd/m/Y H:i'),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // === SEZIONE: CREDITI ===
            fputcsv($handle, ['=== LOG CREDITI ==='], ';');
            fputcsv($handle, ['ID', 'Tipo', 'Importo', 'Saldo Dopo', 'Descrizione', 'Data'], ';');
            foreach ($user->creditLogs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->type ?? '-',
                    number_format($log->amount ?? 0, 2, ',', '.'),
                    number_format($log->balance_after ?? 0, 2, ',', '.'),
                    $log->description ?? '-',
                    local_dt($log->created_at, 'd/m/Y H:i'),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // === SEZIONE: BADGE ===
            fputcsv($handle, ['=== BADGE ==='], ';');
            fputcsv($handle, ['Nome', 'Categoria', 'Stelle', 'Data Ottenimento'], ';');
            foreach ($user->badges as $badge) {
                fputcsv($handle, [
                    $badge->name,
                    $badge->category->label(),
                    $badge->stars ?? 0,
                    local_dt($badge->pivot->earned_at, 'd/m/Y H:i') ?: '-',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $data = [
            'user' => $this->user,
        ];

        // Carica dati in base al tab attivo
        switch ($this->activeTab) {
            case 'competitions':
                $data['competitions'] = $this->user->competitions()
                    ->with('ente')
                    ->orderByDesc('pivot_registered_at')
                    ->paginate(10);
                break;

            case 'enti':
                $data['enti'] = $this->user->enti()
                    ->with('enteProfile')
                    ->orderByDesc('pivot_created_at')
                    ->paginate(10);
                break;

            case 'tracks':
                $data['tracks'] = $this->user->tracks()
                    ->with(['competition', 'segments'])
                    ->orderByDesc('created_at')
                    ->paginate(10);
                break;

            case 'movements':
                $data['movements'] = $this->user->movements()
                    ->with(['partner', 'competition', 'processor'])
                    ->orderByDesc('created_at')
                    ->paginate(10);
                break;

            case 'spese':
                $data['spese'] = $this->user->movements()
                    ->where('status', MovementStatus::APPROVED)
                    ->where('type', MovementType::EXPENSE)
                    ->with(['partner.partnerProfile', 'competition', 'processor'])
                    ->orderByDesc('processed_at')
                    ->paginate(10);
                break;

            case 'credits':
                $data['creditLogs'] = $this->user->creditLogs()
                    ->with(['causer'])
                    ->orderByDesc('created_at')
                    ->paginate(15);
                break;

            case 'badges':
                $data['userBadges'] = $this->user->badges()
                    ->orderByDesc('user_badges.earned_at')
                    ->paginate(15);
                break;
        }

        return view('livewire.admin.users.show', $data);
    }
}
