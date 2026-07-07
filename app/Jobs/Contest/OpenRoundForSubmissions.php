<?php

namespace App\Jobs\Contest;

use App\Models\Round;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OpenRoundForSubmissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;

    public function __construct(
        public Round $round
    ) {}

    public function handle(): void
    {
        // Prevent re-opening already active rounds
        if ($this->round->is_active) {
            Log::info('OpenRoundForSubmissions: Round already active, skipping', [
                'round_id' => $this->round->id,
                'round_number' => $this->round->round_number,
            ]);
            return;
        }

        $this->round->update([
            'is_active' => true,
            'starts_at' => $this->round->starts_at ?? now(),
        ]);

        Log::info('OpenRoundForSubmissions: Round opened for submissions', [
            'round_id' => $this->round->id,
            'round_number' => $this->round->round_number,
            'title' => $this->round->title,
            'ends_at' => $this->round->ends_at,
        ]);
    }
}
