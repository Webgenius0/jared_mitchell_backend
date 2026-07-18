<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'contestant' => $this['contestant'],
            'contestant_id' => $this['contestant_id'],
            'display_name' => $this['display_name'],
            'avatar_url' => $this['avatar_url'],
            'contestable_name' => $this['contestable_name'],
            'total_score' => $this['total_score'],
            'votes_count' => $this['votes_count'],
            'avg_score' => $this['avg_score'] ?? null,
            'rank' => $this['rank'],
        ];
    }
}
