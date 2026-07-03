<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeletionRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected User $requester,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Richiesta cancellazione account - ' . $this->requester->name)
            ->greeting('Nuova richiesta di cancellazione account')
            ->line("L'utente **{$this->requester->name}** ({$this->requester->email}) ha richiesto la cancellazione del proprio account.")
            ->line("**Tipo utente:** {$this->requester->type->label()}")
            ->line("**ID utente:** #{$this->requester->id}")
            ->line("**Data richiesta:** " . local_dt(now(), 'd/m/Y H:i'))
            ->action('Vai al dettaglio utente', url("/admin/users/{$this->requester->id}"))
            ->line('Verifica i dati associati e procedi con la cancellazione manuale se opportuno.');
    }
}
