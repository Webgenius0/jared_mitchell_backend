<?php

namespace App\Notifications\Contest;

use App\Models\Contest\Season;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContestantEliminatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Season $season,
        public int    $roundNumber,
        public int    $rank,
        public string $score,
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
            ->subject("Round {$this->roundNumber} results — {$this->season->title}")
            ->markdown('emails.contest.eliminated', [
                'name'        => $name,
                'seasonTitle' => $this->season->title,
                'roundNumber' => $this->roundNumber,
                'rank'        => $this->rank,
                'score'       => $this->score,
                'seasonUrl'   => url("/contests/{$this->season->slug}"),
            ]);
    }

    /**
     * Get the array representation of the notification (for database/in-app).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'contestant_eliminated',
            'season_id'     => $this->season->id,
            'season_title'  => $this->season->title,
            'season_slug'   => $this->season->slug,
            'round_number'  => $this->roundNumber,
            'rank'          => $this->rank,
            'score'         => $this->score,
            'message'       => "You finished Round {$this->roundNumber} in {$this->season->title} with rank #{$this->rank}.",
        ];
    }
}
