<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'plan_name'    => $this->plan_name,
            'badge_text'   => $this->badge_text,
            'price'        => (float) $this->price,
            'price_suffix' => $this->price_suffix,
            'best_for'     => $this->best_for,
            'outcome_text' => $this->outcome_text,
            'is_featured'  => (bool) $this->is_featured,
            'button_label' => $this->button_label,
            'button_url'   => $this->button_url,
            'sort_order'   => (int) $this->sort_order,
            'feature_groups' => $this->whenLoaded('featureGroups', fn () =>
                $this->featureGroups->map(fn ($group) => [
                    'title' => $group->title,
                    'items' => $group->items->pluck('feature_text')->values(),
                ])->values()
            ),
        ];
    }
}
