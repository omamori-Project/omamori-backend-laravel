<?php

namespace App\Http\Resources\Omamori;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OmamoriCollection extends ResourceCollection
{
    public $collects = OmamoriResource::class;

    public function toArray(Request $request): array
    {
        /** @var LengthAwarePaginator $p */
        $p = $this->resource; 

        return [
            'data' => OmamoriResource::collection($this->collection),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
            ],
        ];
    }
}