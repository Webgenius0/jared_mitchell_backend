<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
    /**
     * Transform the season into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contest_type' => $this->contest_type,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'configuration' => $this->configuration,
            'applications_starts_at' => $this->applications_starts_at?->toIso8601String(),
            'applications_ends_at' => $this->applications_ends_at?->toIso8601String(),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Sponsor when eager-loaded
            'sponsor' => new SponsorResource($this->whenLoaded('sponsor')),

            // Nested rounds when eager-loaded
            'rounds' => RoundResource::collection($this->whenLoaded('rounds')),
        ];
    }
}
