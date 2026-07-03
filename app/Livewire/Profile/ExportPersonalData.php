<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPersonalData extends Component
{
    public function export(): StreamedResponse
    {
        $user = auth()->user()->load([
            'profile',
            'tracks.segments',
            'movements.partner',
            'movements.competition',
            'creditLogs',
            'badges',
            'competitions',
        ]);

        $filename = 'i_miei_dati_goodgo_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($user) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // === PROFILO ===
            fputcsv($handle, ['=== PROFILO ==='], ';');
            fputcsv($handle, ['Campo', 'Valore'], ';');
            fputcsv($handle, ['Nome', $user->name], ';');
            fputcsv($handle, ['Email', $user->email], ';');
            fputcsv($handle, ['Tipo', $user->type->label()], ';');
            fputcsv($handle, ['Crediti', number_format($user->credits, 2, ',', '.')], ';');
            fputcsv($handle, ['Data Registrazione', local_dt($user->created_at, 'd/m/Y H:i')], ';');

            if ($user->profile) {
                fputcsv($handle, ['Username', $user->profile->username ?? '-'], ';');
                fputcsv($handle, ['Data Nascita', $user->profile->birth_date?->format('d/m/Y') ?? '-'], ';');
                fputcsv($handle, ['Indirizzo', $user->profile->address ?? '-'], ';');
                fputcsv($handle, ['Telefono', $user->profile->phone ?? '-'], ';');
            }
            fputcsv($handle, [], ';');

            // === GARE ===
            fputcsv($handle, ['=== GARE ==='], ';');
            fputcsv($handle, ['Nome Gara', 'Stato Iscrizione', 'Data Iscrizione', 'Crediti Gara', 'Distanza (km)', 'CO2 Risparmiata (kg)'], ';');
            foreach ($user->competitions as $comp) {
                fputcsv($handle, [
                    $comp->name,
                    $comp->pivot->status ?? '-',
                    local_dt($comp->pivot->registered_at, 'd/m/Y H:i') ?: '-',
                    number_format($comp->pivot->total_credits ?? 0, 2, ',', '.'),
                    number_format($comp->pivot->total_distance_km ?? 0, 2, ',', '.'),
                    number_format($comp->pivot->total_co2_saved_kg ?? 0, 3, ',', '.'),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // === TRACCE ===
            fputcsv($handle, ['=== TRACCE ==='], ';');
            fputcsv($handle, ['ID', 'Data', 'Stato', 'Distanza (km)', 'CO2 Risparmiata (kg)', 'Calorie'], ';');
            foreach ($user->tracks as $track) {
                fputcsv($handle, [
                    $track->id,
                    local_dt($track->started_at, 'd/m/Y H:i'),
                    $track->status->label(),
                    number_format(($track->total_distance_meters ?? 0) / 1000, 2, ',', '.'),
                    number_format(($track->co2_saved_grams ?? 0) / 1000, 3, ',', '.'),
                    $track->calories_burned ?? 0,
                ], ';');
            }
            fputcsv($handle, [], ';');

            // === MOVIMENTI ===
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

            // === CREDITI ===
            fputcsv($handle, ['=== STORICO CREDITI ==='], ';');
            fputcsv($handle, ['Tipo', 'Importo', 'Saldo Dopo', 'Descrizione', 'Data'], ';');
            foreach ($user->creditLogs as $log) {
                fputcsv($handle, [
                    $log->type ?? '-',
                    number_format($log->amount ?? 0, 2, ',', '.'),
                    number_format($log->balance_after ?? 0, 2, ',', '.'),
                    $log->description ?? '-',
                    local_dt($log->created_at, 'd/m/Y H:i'),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // === BADGE ===
            fputcsv($handle, ['=== BADGE ==='], ';');
            fputcsv($handle, ['Nome', 'Categoria', 'Data Ottenimento'], ';');
            foreach ($user->badges as $badge) {
                fputcsv($handle, [
                    $badge->name,
                    $badge->category->label(),
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
        return view('livewire.profile.export-personal-data');
    }
}
