<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'condition' => $this->condition,
            'language'  => $this->language,
            'price'     => $this->price,
            'stock'     => $this->stock,
            'is_foil'   => $this->is_foil,
            'is_active' => $this->is_active,
            'card'      => CardResource::make($this->whenLoaded('card')),
            'seller'    => [
                'id'   => $this->seller?->id,
                'name' => $this->seller?->name,
            ],
        ];
    }
}
