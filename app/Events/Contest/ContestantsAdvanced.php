<?php

namespace App\Events\Contest;

use App\Models\Contest\RoundTransition;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContestantsAdvanced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RoundTransition $transition,
        public array $advancedContestants,
    ) {}
}
