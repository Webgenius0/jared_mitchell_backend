<?php

namespace App\Notifications\Contest;

use App\Models\Contest\Season;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Season  $season,
        public string  $verdict,
        public ?string $adminNote,
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
        $isApproved = $this->verdict === 'approved';

        $subject = $isApproved
            ? "✅ Your application for {$this->season->title} has been approved!"
            : "Update on your application for {$this->season->title}";

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.contest.application-reviewed', [
                'name'        => $name,
                'seasonTitle' => $this->season->title,
                'verdict'     => $this->verdict,
                'isApproved'  => $isApproved,
                'adminNote'   => $this->adminNote,
                'seasonUrl'   => url("/contests/{$this->season->slug}"),
            ]);
    }

    /**
     * Get the array representation of the notification (for database/in-app).
     */
    public function toArray(object $notifiable): array
    {
        $label = $this->verdict === 'approved' ? 'approved' : 'not approved';

        return [
            'type'         => 'application_reviewed',
            'season_id'    => $this->season->id,
            'season_title' => $this->season->title,
            'verdict'      => $this->verdict,
            'admin_note'   => $this->adminNote,
            'message'      => "Your application for {$this->season->title} has been {$label}.",
        ];
    }
}
