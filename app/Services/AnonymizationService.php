<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnonymizationService
{
    /**
     * Anonimizza e cancella l'utente, mantenendo statistiche aggregate.
     *
     * @param string $action 'self_deletion' o 'admin_deletion'
     * @param int|null $performedBy ID dell'admin che ha eseguito la cancellazione
     */
    public function anonymizeAndDelete(User $user, string $action = 'self_deletion', ?int $performedBy = null): void
    {
        DB::transaction(function () use ($user, $action, $performedBy) {
            // 1. Raccogli dati per il log di audit
            $summary = $this->collectSummary($user);

            // 2. Crea log di anonimizzazione
            DB::table('anonymization_logs')->insert([
                'original_user_id' => $user->id,
                'email_hash' => hash('sha256', $user->email),
                'user_type' => $user->type->value,
                'action' => $action,
                'performed_by' => $performedBy,
                'summary' => json_encode($summary),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Anonimizza tracce (mantieni per statistiche)
            $user->tracks()->update([
                'user_id' => null,
            ]);

            // 4. Anonimizza movimenti (mantieni per statistiche)
            $user->movements()->update([
                'user_id' => null,
            ]);

            // Se è partner, anonimizza anche i movimenti ricevuti come partner
            if ($user->isPartner()) {
                $user->partnerMovements()->update([
                    'partner_id' => null,
                ]);
            }

            // 5. Anonimizza log crediti (mantieni per statistiche)
            $user->creditLogs()->update([
                'user_id' => null,
            ]);

            // 6. Anonimizza competition_user (mantieni statistiche gara)
            DB::table('competition_user')
                ->where('user_id', $user->id)
                ->update(['user_id' => null]);

            // 7. Elimina file tracce dallo storage
            $trackFiles = DB::table('tracks')
                ->whereNull('user_id')
                ->whereNotNull('original_file_path')
                ->pluck('original_file_path');
            foreach ($trackFiles as $filePath) {
                Storage::disk('local')->delete($filePath);
            }
            // Pulisci i path dei file (non servono più)
            DB::table('tracks')
                ->whereNull('user_id')
                ->update(['original_file_path' => null]);

            // 8. Elimina logo partner
            if ($user->isPartner() && $user->partnerProfile?->logo) {
                Storage::disk('public')->delete($user->partnerProfile->logo);
            }

            // 9. Elimina foto profilo Jetstream
            $user->deleteProfilePhoto();

            // 10. Revoca token API
            $user->tokens->each->delete();

            // 11. Elimina dati personali (cascade: profiles, badges, ente_user, etc.)
            $user->delete();
        });

        Log::info("Utente #{$user->id} anonimizzato e cancellato. Azione: {$action}");
    }

    /**
     * Raccoglie un riepilogo dei dati dell'utente prima della cancellazione.
     */
    protected function collectSummary(User $user): array
    {
        return [
            'tracks_count' => $user->tracks()->count(),
            'valid_tracks_count' => $user->validTracks()->count(),
            'total_distance_km' => round($user->validTracks()->sum('total_distance_meters') / 1000, 2),
            'total_co2_saved_kg' => round($user->validTracks()->sum('co2_saved_grams') / 1000, 3),
            'total_calories' => $user->validTracks()->sum('calories_burned'),
            'competitions_count' => $user->competitions()->count(),
            'movements_count' => $user->movements()->count(),
            'badges_count' => $user->badges()->count(),
            'credits_at_deletion' => (float) $user->credits,
            'registered_at' => $user->created_at?->toISOString(),
            'deleted_at' => now()->toISOString(),
        ];
    }
}
