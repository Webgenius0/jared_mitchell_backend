<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? '',
            'username' => $this->username ?? '',
            'address' => $this->address ?? '',
            'biography' => $this->biography ?? '',
            'tagline' => $this->tagline ?? '',
            'business_description' => $this->business_description ?? '',
            'website_link' => $this->website_link ?? '',
            'social_links' => $this->social_links ?? [
                'youtube' => '',
                'facebook' => '',
                'instagram' => '',
            ],
            'avatar' => $this->avatar
                ? asset($this->avatar)
                : asset('admin/default/user.jpg'),
        ];
    }
}
