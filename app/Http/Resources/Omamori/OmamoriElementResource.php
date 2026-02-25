<?php

namespace App\Http\Resources\Omamori;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OmamoriElementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $props = $this->props ?? [];
        $stampUrl = null;

        if ($this->type === 'stamp') {
            $assetKey = $props['asset_key'] ?? null;

            if ($assetKey) {
                $disk = Storage::disk('public');
                $path = "stamps/{$assetKey}.png";

                if ($disk->exists($path)) {
                    $stampUrl = Storage::url($path);   
                    $props['url'] = $stampUrl;      
                }
            }
        }

        return [
            'id'         => $this->id,
            'omamori_id' => $this->omamori_id,
            'type'       => $this->type,
            'layer'      => $this->layer,
            'props'      => $props,
            'transform'  => $this->transform ?? [],
            'stamp_url'  => $stampUrl,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}