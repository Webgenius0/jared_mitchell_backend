<?php

namespace App\Notifications\Contest;

use App\Models\Contest\Season;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Season  $season,
        public string  $applicantName,
        public ?int    $applicationId,
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
            ->subject("New application for {$this->season->title}")
            ->markdown('emails.contest.application-submitted', [
                'seasonTitle'    => $this->season->title,
                'applicantName'  => $this->applicantName,
                'adminUrl'       => url("/admin/contests/seasons/{$this->season->id}/applications"),
            ]);
    }

    /**
     * Get the array representation of the notification (for database/in-app).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'application_submitted',
            'season_id'      => $this->season->id,
            'season_title'   => $this->season->title,
            'applicant_name' => $this->applicantName,
            'message'        => "New application received from {$this->applicantName} for {$this->season->title}.",
        ];
    }
}
