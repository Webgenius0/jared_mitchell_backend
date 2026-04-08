<?php

namespace App\Mail\Api;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int|string $otp,
        public readonly User $user,
        public readonly string $mailSubject,
        public readonly string $headerTitle,
        public readonly string $bodyMessage,
        public readonly int $expiresInMinutes = 60,
        public readonly ?string $actionUrl = null,
        public readonly ?string $actionLabel = null,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.api.register-otp',
            with: [
                'otp' => $this->otp,
                'user' => $this->user,
                'recipientName' => $this->recipientName(),
                'subject' => $this->mailSubject,
                'header_message' => $this->headerTitle,
                'header_title' => $this->headerTitle,
                'bodyMessage' => $this->bodyMessage,
                'preheader' => $this->preheader(),
                'expiresIn' => $this->expiresInMinutes,
                'actionUrl' => $this->actionUrl,
                'actionLabel' => $this->actionLabel,
                'appName' => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function recipientName(): string
    {
        $profileName = data_get($this->user, 'profile.name');

        if (is_string($profileName) && trim($profileName) !== '') {
            return trim($profileName);
        }

        $email = (string) $this->user->email;

        if ($email !== '') {
            return Str::before($email, '@');
        }

        return 'there';
    }

    private function preheader(): string
    {
        return $this->bodyMessage . ' Your OTP is ' . $this->otp . '. It expires in ' . $this->expiresInMinutes . ' minutes.';
    }
}