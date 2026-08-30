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
        $htmlContent = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Welcome to Our Social Image</title>
            </head>
            <body style="margin: 0; padding: 20px 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
                <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 680px; margin: 0 auto; background-color: #ffffff; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 32px 24px; text-align: center; border-bottom: 4px solid #6366f1;">
                            <div style="display: inline-block; color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; background: rgba(255,255,255,0.08); padding: 8px 22px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.15);">OUR SOCIAL IMAGE</div>
                            <div style="color: #94a3b8; font-size: 13px; margin-top: 8px; letter-spacing: 1px; text-transform: uppercase;">Official Community & Artist Platform</div>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 36px; line-height: 1.75; color: #334155;">
                            <h2 style="color: #0f172a; margin-top: 0; font-size: 24px; font-weight: 700;">Welcome to the Movement! 🎉</h2>
                            <p style="margin-bottom: 18px; font-size: 16px;">Thank you for subscribing to <strong>Our Social Image</strong>. You are now officially part of an exclusive community dedicated to empowering artists, live streaming, and music industry innovation.</p>
                            
                            <div style="background-color: #f8fafc; border-left: 4px solid #6366f1; padding: 18px 20px; margin: 24px 0; border-radius: 0 4px 4px 0;">
                                <h4 style="margin: 0 0 8px 0; color: #0f172a;">Here is what you can expect from us:</h4>
                                <ul style="margin: 0; padding-left: 20px; color: #475569; font-size: 15px;">
                                    <li style="margin-bottom: 6px;">🎤 <strong>Artist & Business Spotlights:</strong> Discover groundbreaking indie talents and partners.</li>
                                    <li style="margin-bottom: 6px;">🏆 <strong>Contest & Voting Reveals:</strong> Exclusive updates on live voting rounds and winners.</li>
                                    <li style="margin-bottom: 6px;">📺 <strong>Live Stream Events:</strong> Direct access to upcoming live performance broadcasts.</li>
                                </ul>
                            </div>

                            <div style="text-align: center; margin: 36px 0 16px 0;">
                                <a href="https://admin.oursocialimage.net" target="_blank" style="display: inline-block; background: #6366f1; color: #ffffff; font-size: 16px; font-weight: 700; text-decoration: none; padding: 15px 36px; border-radius: 4px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);">Explore Our Platform →</a>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 32px 24px; text-align: center; color: #94a3b8; font-size: 13px;">
                            <p style="margin: 0 0 6px 0; color: #ffffff; font-weight: bold;">OUR SOCIAL IMAGE</p>
                            <p style="margin: 0 0 12px 0;">© ' . date('Y') . ' Our Social Image. All rights reserved.</p>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                You received this email because you subscribed on our platform.<br>
                                Want to stop receiving emails? <a href="' . $this->unsubscribeUrl . '" target="_blank" style="color: #6366f1; text-decoration: underline;">Unsubscribe here</a>.
                            </p>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        ';

        return new Content(
            htmlString: $htmlContent
        );
    }
}
