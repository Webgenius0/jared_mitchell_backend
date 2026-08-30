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
        if (str_starts_with($this->templateStyle, 'custom_')) {
            $templateId = (int) str_replace('custom_', '', $this->templateStyle);
            $customTemplate = \App\Models\EmailTemplate::find($templateId);

            if ($customTemplate) {
                $customHtml = $customTemplate->html_content;

                // Smart Injection 1: If custom template contains {{content}} variable
                if (str_contains($customHtml, '{{content}}') || str_contains($customHtml, '{{ content }}')) {
                    $customHtml = str_replace(['{{content}}', '{{ content }}'], $this->htmlContent, $customHtml);
                }
                // Smart Injection 2: Replace default text block container or paragraph
                elseif (preg_match('/<div[^>]*data-type="text"[^>]*>.*?<\/div>/s', $customHtml)) {
                    $replacement = '<div data-type="text" style="padding: 20px; color: #334155;">'
                        . '<h2 style="color: #0f172a; margin-top:0;">' . e($this->emailSubject) . '</h2>'
                        . $this->htmlContent
                        . '</div>';
                    $customHtml = preg_replace('/<div[^>]*data-type="text"[^>]*>.*?<\/div>/s', $replacement, $customHtml, 1);
                }
                // Smart Injection 3: Replace "Welcome to Our Latest Update" or "New Feature Highlight" copy
                elseif (str_contains($customHtml, 'Welcome to Our Latest Update') || str_contains($customHtml, 'New Feature Highlight')) {
                    $pattern = '/<h3[^>]*>(?:Welcome to Our Latest Update|New Feature Highlight)<\/h3>\s*<p[^>]*>.*?<\/p>/s';
                    $customHtml = preg_replace($pattern, $this->htmlContent, $customHtml, 1);
                }
                // Smart Injection 4: Fallback - wrap customHtml inside email layout container
                else {
                    $customHtml = '<div style="max-width: 720px; margin: 0 auto; background: #ffffff; padding: 20px; font-family: sans-serif;">'
                        . $customHtml
                        . '<div style="padding: 20px 0; border-top: 1px solid #e2e8f0; margin-top: 20px;">' . $this->htmlContent . '</div>'
                        . '</div>';
                }

                return new Content(
                    htmlString: $customHtml
                );
            }
        }

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
