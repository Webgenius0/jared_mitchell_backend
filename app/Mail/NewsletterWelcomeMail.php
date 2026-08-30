<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $recipientEmail;
    public string $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;
        $this->unsubscribeUrl = route('admin.newsletters.unsubscribe', ['email' => $recipientEmail]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Welcome to Our Social Image Community!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter_welcome',
            with: [
                'recipientEmail' => $this->recipientEmail,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]
        );
    }
}
