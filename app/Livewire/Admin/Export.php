<?php

namespace App\Livewire\Admin;

use App\Enums\CompetitionStatus;
use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Models\Competition;
use App\Models\Movement;
use App\Models\User;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Export extends Component
{
    public string $exportType = 'users';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function getRecordCountProperty(): int
    {
        return $this->buildQuery()->count();
    }

    public function export(): StreamedResponse
    {
        return match ($this->exportType) {
            'users' => $this->exportUsers(),
            'competitions' => $this->exportCompetitions(),
            'movements' => $this->exportMovements(),
            'spese' => $this->exportSpese(),
        };
    }

    // ==================== EXPORT METHODS ====================

    protected function exportUsers(): StreamedResponse
    {
        $records = $this->buildQuery()->get();
        $filename = 'utenti_' . now()->format('Y-m-d_His') . '.csv';

        return $this->streamCsv($filename, [
            'ID',
            'Nome',
            'Email',
            'Tipo',
            'Piattaforma',
            'Crediti',
            'Email Verificata',
            'Data Registrazione',
            'Ultimo Accesso',
        ], $records, function (User $user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                $user->type->label(),
                $user->platform ? ucfirst($user->platform) : 'Backoffice',
                number_format($user->credits, 2, ',', '.'),
                $user->email_verified_at ? 'Sì' : 'No',
                local_dt($user->created_at, 'd/m/Y H:i'),
                local_dt($user->last_login_at, 'd/m/Y H:i') ?: '-',
            ];
        });
    }

    protected function exportCompetitions(): StreamedResponse
    {
        $records = $this->buildQuery()->get();
        $filename = 'gare_' . now()->format('Y-m-d_His') . '.csv';

        return $this->streamCsv($filename, [
            'ID',
            'Nome',
            'Slug',
            'Tipo',
            'Estensione',
            'Stato',
            'Data Inizio',
            'Data Fine',
            'Iscritti',
            'Crediti Distribuiti',
        ], $records, function (Competition $competition) {
            return [
                $competition->id,
                $competition->name,
                $competition->slug,
                $competition->competition_type?->label() ?? '-',
                $competition->extension_type?->label() ?? '-',
                $competition->status->label(),
                $competition->start_date?->format('d/m/Y'),
                $competition->end_date?->format('d/m/Y'),
                $competition->users_count ?? 0,
                number_format($competition->total_credits ?? 0, 2, ',', '.'),
            ];
        });
    }

    protected function exportMovements(): StreamedResponse
    {
        $records = $this->buildQuery()->get();
        $filename = 'movimenti_' . now()->format('Y-m-d_His') . '.csv';

        return $this->streamCsv($filename, [
            'ID',
            'Utente',
            'Email Utente',
            'Tipo',
            'Stato',
            'Crediti',
            'Euro',
            'Gara',
            'Partner',
            'Descrizione',
            'Data Richiesta',
            'Data Gestione',
        ], $records, function (Movement $movement) {
            return [
                $movement->id,
                $movement->user?->name ?? '-',
                $movement->user?->email ?? '-',
                $movement->type->label(),
                $movement->status->label(),
                number_format($movement->credits_amount, 2, ',', '.'),
                number_format($movement->euro_amount ?? 0, 2, ',', '.'),
                $movement->competition?->name ?? '-',
                $movement->partner?->name ?? '-',
                $movement->description ?? '-',
                local_dt($movement->created_at, 'd/m/Y H:i'),
                local_dt($movement->processed_at, 'd/m/Y H:i'),
            ];
        });
    }

    protected function exportSpese(): StreamedResponse
    {
        $records = $this->buildQuery()->get();
        $filename = 'spese_' . now()->format('Y-m-d_His') . '.csv';

        return $this->streamCsv($filename, [
            'ID',
            'Utente',
            'Email Utente',
            'Partner',
            'Crediti',
            'Euro',
            'Tasso Cambio',
            'Gara',
            'Descrizione',
            'Data Richiesta',
            'Data Approvazione',
            'Approvato da',
        ], $records, function (Movement $movement) {
            return [
                $movement->id,
                $movement->user?->name ?? '-',
                $movement->user?->email ?? '-',
                $movement->partner?->name ?? '-',
                number_format($movement->credits_amount, 2, ',', '.'),
                number_format($movement->euro_amount ?? 0, 2, ',', '.'),
                number_format($movement->exchange_rate ?? 0, 4, ',', '.'),
                $movement->competition?->name ?? '-',
                $movement->description ?? '-',
                local_dt($movement->created_at, 'd/m/Y H:i'),
                local_dt($movement->processed_at, 'd/m/Y H:i'),
                $movement->processor?->name ?? '-',
            ];
        });
    }

    // ==================== QUERY BUILDER ====================

    protected function buildQuery()
    {
        return match ($this->exportType) {
            'users' => $this->buildUsersQuery(),
            'competitions' => $this->buildCompetitionsQuery(),
            'movements' => $this->buildMovementsQuery(),
            'spese' => $this->buildSpeseQuery(),
        };
    }

    protected function buildUsersQuery()
    {
        $query = User::query()->orderBy('id');

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    protected function buildCompetitionsQuery()
    {
        $query = Competition::query()
            ->withCount('users')
            ->withSum('users as total_credits', 'competition_user.total_credits')
            ->orderBy('id');

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    protected function buildMovementsQuery()
    {
        $query = Movement::query()
            ->with(['user', 'partner', 'competition'])
            ->orderBy('id');

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    protected function buildSpeseQuery()
    {
        $query = Movement::query()
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->with(['user', 'partner', 'competition', 'processor'])
            ->orderBy('id');

        if ($this->dateFrom) {
            $query->whereDate('processed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('processed_at', '<=', $this->dateTo);
        }

        return $query;
    }

    // ==================== HELPERS ====================

    protected function streamCsv(string $filename, array $headers, $records, callable $rowMapper): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $records, $rowMapper) {
            $handle = fopen('php://output', 'w');

            // BOM per UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($handle, $headers, ';');

            // Rows
            foreach ($records as $record) {
                fputcsv($handle, $rowMapper($record), ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.export');
    }
}
