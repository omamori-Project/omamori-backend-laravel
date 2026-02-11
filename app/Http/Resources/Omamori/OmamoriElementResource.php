<?php

namespace App\Http\Resources\Omamori;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OmamoriElementResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'omamori_id' => $this->omamori_id,
            'type'       => $this->type,      // text | stamp | background
            'layer'      => $this->layer,     // int
            'props'      => $this->props ?? [],
            'transform'  => $this->transform ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}