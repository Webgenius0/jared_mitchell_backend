<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Key contents by field_key so frontend can access e.g. section.contents.heading
        $contents = [];
        if ($this->relationLoaded('contents')) {
            foreach ($this->contents as $content) {
                $contents[$content->field_key] = [
                    'type'  => $content->field_type,
                    'value' => $content->value,
                    'url'   => in_array($content->field_type, ['image', 'video'], true)
                        ? asset('storage/' . $content->value)
                        : null,
                ];
            }
        }

        return [
            'id'          => $this->id,
            'section_key' => $this->section_key,
            'label'       => $this->label,
            'order'       => (int) $this->order,
            'contents'    => $contents,
            'items'       => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'order' => (int) $item->order,
                    'data'  => $item->data,
                ])->values()
            ),
        ];
    }
}
