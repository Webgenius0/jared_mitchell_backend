<?php

namespace App\Jobs;

use App\Mail\NewsletterBroadcastMail;
use App\Models\Newsletter;
use App\Models\NewsletterBroadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $broadcastId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $broadcastId)
    {
        $this->broadcastId = $broadcastId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $broadcast = NewsletterBroadcast::find($this->broadcastId);

        if (!$broadcast) {
            Log::error("NewsletterBroadcast #{$this->broadcastId} not found.");
            return;
        }

        $broadcast->update(['status' => 'processing']);

        $sentCount = 0;
        $failedCount = 0;

        try {
            Newsletter::where('status', 'active')->chunk(50, function ($subscribers) use ($broadcast, &$sentCount, &$failedCount) {
                foreach ($subscribers as $subscriber) {
                    try {
                        Mail::to($subscriber->email)->send(new NewsletterBroadcastMail(
                            emailSubject: $broadcast->subject,
                            htmlContent: $broadcast->html_content,
                            bannerImageUrl: $broadcast->banner_image_url,
                            ctaButtonText: $broadcast->cta_button_text,
                            ctaButtonUrl: $broadcast->cta_button_url,
                            primaryColor: $broadcast->primary_color ?? '#6366f1',
                            recipientEmail: $subscriber->email,
                            templateStyle: $broadcast->template_style ?? 'modern'
                        ));

                        $sentCount++;
                    } catch (\Throwable $e) {
                        Log::error("Failed to send newsletter broadcast to {$subscriber->email}: " . $e->getMessage());
                        $failedCount++;
                    }
                }
            });

            $broadcast->update([
                'sent_count'   => $sentCount,
                'failed_count' => $failedCount,
                'status'       => 'completed',
            ]);

            Log::info("NewsletterBroadcast #{$broadcast->id} completed. Sent: {$sentCount}, Failed: {$failedCount}");
        } catch (\Throwable $ex) {
            Log::error("NewsletterBroadcast #{$broadcast->id} failed: " . $ex->getMessage());
            $broadcast->update([
                'sent_count'   => $sentCount,
                'failed_count' => $failedCount,
                'status'       => 'failed',
            ]);
        }
    }
}
