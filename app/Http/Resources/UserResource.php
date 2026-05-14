<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email ?? '',
            'status' => $this->status ?? 'inactive',
            'role' => $this->getRoleNames()->first() ?? null,

            // profile resource
            'profile' => $this->profile
                ? new ProfileResource($this->profile)
                : [
                    'id' => null,
                    'name' => '',
                    'username' => '',
                    'slug' => '',
                    'avatar' => asset('admin/default/user.jpg'),
                ],

            'artist_category' => $this->artistCategory ? [
                'id' => $this->artistCategory->id,
                'name' => $this->artistCategory->name,
                'slug' => $this->artistCategory->slug,
            ] : null,
        ];
    }
}
