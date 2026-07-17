<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoundResource extends JsonResource
{
    /**
     * Transform the round into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'season_id' => $this->season_id,
            'round_number' => $this->round_number,
            'title' => $this->title,
            'goal' => $this->goal,
            'requirements' => $this->requirements,
            'voting_strategy' => $this->voting_strategy,
            'submission_type' => $this->submission_type,
            'submission_requirements' => $this->submission_requirements,
            'advance_limit' => $this->advance_limit,
            'elimination_rule' => $this->elimination_rule,
            'advancement_config' => $this->advancement_config,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'voting_ends_at' => $this->voting_ends_at?->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
