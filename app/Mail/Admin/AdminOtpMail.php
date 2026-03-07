<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $adminName = 'Admin',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Password Reset OTP — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.otp',
            with: [
                'otp'       => $this->otp,
                'adminName' => $this->adminName,
                'expiresIn' => 10, // minutes
                'appName'   => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
