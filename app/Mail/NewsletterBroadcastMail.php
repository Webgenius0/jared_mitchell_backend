<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $htmlContent;
    public ?string $bannerImageUrl;
    public ?string $ctaButtonText;
    public ?string $ctaButtonUrl;
    public string $primaryColor;
    public string $unsubscribeUrl;
    public string $templateStyle;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $emailSubject,
        string $htmlContent,
        ?string $bannerImageUrl = null,
        ?string $ctaButtonText = null,
        ?string $ctaButtonUrl = null,
        string $primaryColor = '#6366f1',
        ?string $recipientEmail = null,
        string $templateStyle = 'modern'
    ) {
        $this->emailSubject   = $emailSubject;
        $this->htmlContent    = $htmlContent;
        $this->bannerImageUrl = $bannerImageUrl;
        $this->ctaButtonText  = $ctaButtonText;
        $this->ctaButtonUrl   = $ctaButtonUrl;
        $this->primaryColor   = $primaryColor;
        $this->templateStyle  = $templateStyle;

        $baseUrl = config('app.url', 'https://admin.oursocialimage.net');
        $this->unsubscribeUrl = $baseUrl . '/api/v1/newsletters/unsubscribe?email=' . urlencode($recipientEmail ?? '');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $viewName = match ($this->templateStyle) {
            'minimalist'  => 'emails.templates.minimalist_clean',
            'dark'        => 'emails.templates.dark_cyber',
            'promotional' => 'emails.templates.promotional_card',
            default       => 'emails.newsletter_template',
        };

        return new Content(
            view: $viewName,
            with: [
                'subject'        => $this->emailSubject,
                'htmlContent'    => $this->htmlContent,
                'bannerImageUrl' => $this->bannerImageUrl,
                'ctaButtonText'  => $this->ctaButtonText,
                'ctaButtonUrl'   => $this->ctaButtonUrl,
                'primaryColor'   => $this->primaryColor,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
