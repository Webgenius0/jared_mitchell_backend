<?php

namespace App\Jobs\Contest;

use App\Models\ContestApplication;
use App\Models\Contest\Season;
use App\Services\Contest\AiReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BatchProcessAiReviews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries   = 2;

    /**
     * @param Season|null $season  If null, processes pending reviews across all seasons
     */
    public function __construct(
        public ?Season $season = null,
        public int     $limit = 50,
    ) {}

    public function handle(AiReviewService $reviewService): void
    {
        $query = ContestApplication::whereNull('ai_reviewed_at')
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) {
                $q->whereNull('metadata->ai_retries')
                  ->orWhere('metadata->ai_retries', '<', 3);
            });

        if ($this->season) {
            $query->whereHas('season', function ($q) {
                $q->where('seasons.id', $this->season->id);
            });
        }

        $applications = $query->limit($this->limit)->get();

        if ($applications->isEmpty()) {
            Log::info('BatchProcessAiReviews: No pending reviews found');
            return;
        }

        Log::info('BatchProcessAiReviews: Dispatching reviews', [
            'count' => $applications->count(),
        ]);

        foreach ($applications as $application) {
            ProcessAiReview::dispatch($application)
                ->onQueue('high');
        }
    }
}
