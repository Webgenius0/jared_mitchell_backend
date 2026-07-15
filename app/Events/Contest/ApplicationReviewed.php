<?php

namespace App\Events\Contest;

use App\Models\Contest\AiReview;
use App\Models\ContestApplication;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationReviewed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ContestApplication $application, public AiReview $review, public string $verdict) {}
}
