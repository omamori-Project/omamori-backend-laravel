<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdentityResource extends JsonResource
{
    /**
     * Identity 정보를 배열로 변환
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'email' => $this->email,
            'profile' => $this->profile,
            'linked_at' => $this->linked_at,
            'last_used_at' => $this->last_used_at,
        ];
    }
}