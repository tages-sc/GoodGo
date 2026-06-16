<?php

namespace App\Notifications;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BadgeEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Badge $badge
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $displayName = $this->badge->display_name;

        return (new MailMessage)
            ->subject("Nuovo Badge: {$displayName}!")
            ->greeting("Complimenti {$notifiable->name}!")
            ->line("Hai appena ottenuto il badge **{$displayName}**!")
            ->line($this->badge->description)
            ->line('Continua così per sbloccare nuovi traguardi!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'badge_earned',
            'badge_id' => $this->badge->id,
            'badge_name' => $this->badge->display_name,
            'badge_slug' => $this->badge->slug,
            'badge_description' => $this->badge->description,
            'badge_category' => $this->badge->category->value,
            'badge_stars' => $this->badge->stars,
        ];
    }
}
