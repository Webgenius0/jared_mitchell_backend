<?php

namespace App\Notifications\Contest;

use App\Models\Contest\Season;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SeasonStartingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Season  $season,
        public ?string $firstRoundTitle,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->profile?->name ?? $notifiable->email ?? 'there';

        return (new MailMessage)
            ->subject("🎬 {$this->season->title} is starting!")
            ->markdown('emails.contest.season-starting', [
                'name'           => $name,
                'seasonTitle'    => $this->season->title,
                'firstRoundTitle'=> $this->firstRoundTitle,
                'seasonUrl'      => url("/contests/{$this->season->slug}"),
            ]);
    }

    /**
     * Get the array representation of the notification (for database/in-app).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'              => 'season_starting',
            'season_id'         => $this->season->id,
            'season_title'      => $this->season->title,
            'season_slug'       => $this->season->slug,
            'first_round_title' => $this->firstRoundTitle,
            'message'           => "{$this->season->title} is starting! Get ready to compete.",
        ];
    }
}
