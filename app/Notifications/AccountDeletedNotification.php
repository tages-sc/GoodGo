<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeletedNotification extends Notification
{

    public function __construct(
        protected string $userName,
        protected string $userEmail,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Account GoodGo cancellato')
            ->greeting("Ciao {$this->userName},")
            ->line('Confermiamo che il tuo account GoodGo è stato cancellato con successo.')
            ->line('Tutti i tuoi dati personali sono stati rimossi dal nostro sistema.')
            ->line('Le statistiche aggregate (distanze, emissioni risparmiate, ecc.) sono state mantenute in forma anonima per finalità statistiche.')
            ->line('Se non hai richiesto tu questa cancellazione, contattaci immediatamente.')
            ->salutation('Il team GoodGo');
    }
}
