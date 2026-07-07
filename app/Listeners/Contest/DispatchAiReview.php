<?php

namespace App\Listeners\Contest;

use App\Events\Contest\ApplicationSubmitted;
use App\Jobs\Contest\ProcessAiReview;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchAiReview implements ShouldQueue
{
    public $queue = 'high';

    public function handle(ApplicationSubmitted $event): void
    {
        // Dispatch the AI review job to the high-priority queue
        ProcessAiReview::dispatch($event->application)
            ->onQueue('high');
    }
}
