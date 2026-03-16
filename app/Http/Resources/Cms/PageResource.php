<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Cms\SectionResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'sections'         => SectionResource::collection(
                $this->whenLoaded('sections')
            ),
        ];
    }
}
