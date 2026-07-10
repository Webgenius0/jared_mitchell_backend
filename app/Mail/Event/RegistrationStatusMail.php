<?php

namespace App\Mail\Event;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EventRegistration $registration,
        public string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->newStatus) {
            'confirmed' => '✅ Registration Confirmed — ' . $this->registration->event?->title,
            'cancelled' => '❌ Registration Cancelled — ' . $this->registration->event?->title,
            default     => 'Registration Update — ' . $this->registration->event?->title,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $registration = $this->registration;
        $registration->loadMissing(['event', 'ticketTier']);

        return new Content(
            view: 'emails.event.registration-status',
            with: [
                'registration' => $registration,
                'event'        => $registration->event,
                'ticketTier'   => $registration->ticketTier,
                'status'       => $this->newStatus,
                'customerName' => trim($registration->first_name . ' ' . $registration->last_name) ?: 'Valued Customer',
                'appName'      => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
