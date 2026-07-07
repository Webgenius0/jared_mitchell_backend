<?php

namespace App\Jobs\Contest;

use App\Models\ContestApplication;
use App\Services\Contest\AiReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessAiReview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries   = 3;
    public $backoff = [5, 15, 30];
    public $maxExceptions = 2;

    public function __construct(
        public ContestApplication $application
    ) {}

    public function handle(AiReviewService $reviewService): void
    {
        // Skip if already reviewed
        if ($this->application->ai_reviewed_at !== null) {
            Log::info('ProcessAiReview: Application already reviewed, skipping', [
                'application_id' => $this->application->id,
            ]);
            return;
        }

        try {
            $reviewService->review($this->application);
        } catch (Throwable $e) {
            Log::error('ProcessAiReview failed', [
                'application_id' => $this->application->id,
                'error'          => $e->getMessage(),
            ]);

            // Mark as needs_review so it doesn't stall
            $this->application->update([
                'ai_reviewed_at' => now(),
                'ai_verdict'     => 'needs_review',
                'ai_confidence'  => 0.0,
                'status'         => 'needs_review',
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $this->application->update([
            'ai_reviewed_at' => now(),
            'ai_verdict'     => 'needs_review',
            'ai_confidence'  => 0.0,
            'status'         => 'needs_review',
        ]);

        Log::error('ProcessAiReview permanently failed', [
            'application_id' => $this->application->id,
            'error'          => $e->getMessage(),
        ]);
    }
}
