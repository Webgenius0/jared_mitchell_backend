<?php

namespace App\Notifications\Contest;

use App\Models\Contest\Season;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoundEndedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Season  $season,
        public int     $roundNumber,
        public string  $roundTitle,
        public int     $advancedCount,
        public int     $eliminatedCount,
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
        return (new MailMessage)
            ->subject("Round {$this->roundNumber} ended — {$this->season->title}")
            ->markdown('emails.contest.round-ended', [
                'seasonTitle'     => $this->season->title,
                'roundNumber'     => $this->roundNumber,
                'roundTitle'      => $this->roundTitle,
                'advancedCount'   => $this->advancedCount,
                'eliminatedCount' => $this->eliminatedCount,
                'adminUrl'        => url("/admin/contests/seasons/{$this->season->id}"),
            ]);
    }

    /**
     * Get the array representation of the notification (for database/in-app).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'round_ended',
            'season_id'        => $this->season->id,
            'season_title'     => $this->season->title,
            'round_number'     => $this->roundNumber,
            'round_title'      => $this->roundTitle,
            'advanced_count'   => $this->advancedCount,
            'eliminated_count' => $this->eliminatedCount,
            'message'          => "Round {$this->roundNumber} in {$this->season->title} ended — {$this->advancedCount} advanced, {$this->eliminatedCount} eliminated.",
        ];
    }
}
