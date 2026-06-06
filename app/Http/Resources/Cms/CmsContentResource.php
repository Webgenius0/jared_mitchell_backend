<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CmsContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'section' => $this->section instanceof \App\Enums\CmsSection ? $this->section->value : $this->section,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'description' => $this->description,
            'image' => $this->image,
            'bg' => $this->bg,
            // 'video' => $this->video ? (filter_var($this->video, FILTER_VALIDATE_URL) ? $this->video : url($this->video)) : null,
            'video' => $this->video
                ? (
                    filter_var($this->video, FILTER_VALIDATE_URL)
                    ? $this->video
                    : url(Storage::url($this->video))
                )
                : null,
            'metadata' => $this->formatMetadata($this->metadata),
        ];
    }

    /**
     * Recursively format metadata to ensure URLs are absolute for images/files.
     */
    private function formatMetadata($metadata)
    {
        if (is_array($metadata)) {
            foreach ($metadata as $key => $value) {
                if (is_array($value)) {
                    $metadata[$key] = $this->formatMetadata($value);
                } elseif (is_string($value) && !empty($value)) {
                    // If the key suggests an image or file and it's not a full URL, make it absolute.
                    if (in_array($key, ['image', 'file', 'icon_path', 'bg', 'video']) || str_contains($key, 'image_file')) {
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $metadata[$key] = url($value);
                        }
                    }
                }
            }
        }
        return $metadata;
    }
}
