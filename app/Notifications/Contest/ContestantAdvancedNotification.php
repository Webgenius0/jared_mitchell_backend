<?php

namespace App\Notifications\Contest;

use App\Models\Contest\Season;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContestantAdvancedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Season $season,
        public int    $fromRoundNumber,
        public int    $toRoundNumber,
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
            ->subject("🎉 You advanced to Round {$this->toRoundNumber} in {$this->season->title}")
            ->markdown('emails.contest.advanced', [
                'name'           => $name,
                'seasonTitle'    => $this->season->title,
                'fromRound'      => $this->fromRoundNumber,
                'toRound'        => $this->toRoundNumber,
                'rank'           => $this->rank,
                'score'          => $this->score,
                'seasonUrl'      => url("/contests/{$this->season->slug}"),
            ]);
    }

    /**
     * Get the array representation of the notification (for database/in-app).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'contestant_advanced',
            'season_id'        => $this->season->id,
            'season_title'     => $this->season->title,
            'season_slug'      => $this->season->slug,
            'from_round_number'=> $this->fromRoundNumber,
            'to_round_number'  => $this->toRoundNumber,
            'rank'             => $this->rank,
            'score'            => $this->score,
            'message'          => "You advanced from Round {$this->fromRoundNumber} to Round {$this->toRoundNumber} in {$this->season->title}!",
        ];
    }
}
