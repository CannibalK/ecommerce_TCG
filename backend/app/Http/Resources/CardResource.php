<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'collector_number' => $this->collector_number,
            'rarity'           => $this->rarity,
            'image_url'        => $this->image_url,
            'attributes'       => $this->attributes,
            'set'              => SetResource::make($this->whenLoaded('set')),
            // Solo incluye products si fueron cargados (evita N+1 accidental)
            'products'         => ProductResource::collection($this->whenLoaded('products')),
            'min_price'        => $this->whenCounted('products') ? null : $this->products?->where('is_active', true)->min('price'),
        ];
    }
}
