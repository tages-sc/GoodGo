<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use App\Notifications\AccountDeletedNotification;
use App\Services\AnonymizationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    public function __construct(
        protected AnonymizationService $anonymizationService,
    ) {}

    /**
     * Delete the given user and all associated data (GDPR - Diritto all'oblio).
     *
     * Anonimizza i dati statistici (tracce, movimenti, crediti) prima di cancellare
     * l'utente, per mantenere le statistiche aggregate del sistema.
     */
    public function delete(User $user): void
    {
        // Salva dati per l'email di conferma prima della cancellazione
        $userName = $user->name;
        $userEmail = $user->email;

        // Anonimizza e cancella
        $this->anonymizationService->anonymizeAndDelete($user, 'self_deletion');

        // Invia email di conferma cancellazione
        try {
            Notification::route('mail', $userEmail)
                ->notify(new AccountDeletedNotification($userName, $userEmail));
        } catch (\Throwable $e) {
            Log::warning("Email conferma cancellazione non inviata a {$userEmail}: {$e->getMessage()}");
        }
    }
}
