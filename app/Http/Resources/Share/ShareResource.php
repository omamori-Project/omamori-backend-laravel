<?php

namespace App\Http\Resources\Share;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $token = (string) $this->token;

        return [
            'id'         => $this->id,
            'token'      => $token,
            'share_url'  => url("/api/v1/public/shares/{$token}"),
            'is_active'  => (bool) $this->is_active,
            'expires_at' => $this->expires_at?->toISOString(),
            'view_count' => (int) $this->view_count,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}