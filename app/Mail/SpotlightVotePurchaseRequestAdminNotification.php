<?php

namespace App\Mail;

use App\Models\Spotlight\SpotlightVotePurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpotlightVotePurchaseRequestAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public SpotlightVotePurchase $purchase;

    /**
     * Create a new message instance.
     */
    public function __construct(SpotlightVotePurchase $purchase)
    {
        $this->purchase = $purchase->load(['user.profile', 'nominee.spotlightable', 'package']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Vote Purchase Request #' . $this->purchase->id . ' — Pending Approval',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.spotlight-vote-purchase-request',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
