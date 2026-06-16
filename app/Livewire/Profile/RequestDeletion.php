<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Notifications\DeletionRequestNotification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RequestDeletion extends Component
{
    public bool $confirmingRequest = false;

    public function confirmRequest(): void
    {
        $this->confirmingRequest = true;
    }

    public function sendRequest(): void
    {
        $user = auth()->user();

        // Salva la data della richiesta
        $user->update(['deletion_requested_at' => now()]);

        // Invia email a tutti i super admin
        $admins = User::where('type', \App\Enums\UserType::SUPER_ADMIN)->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new DeletionRequestNotification($user));
            } catch (\Throwable $e) {
                Log::warning("Email richiesta cancellazione non inviata ad admin #{$admin->id}: {$e->getMessage()}");
            }
        }

        $this->confirmingRequest = false;

        session()->flash('status', 'deletion-requested');
    }

    public function render()
    {
        return view('livewire.profile.request-deletion', [
            'deletionRequestedAt' => auth()->user()->deletion_requested_at,
        ]);
    }
}
