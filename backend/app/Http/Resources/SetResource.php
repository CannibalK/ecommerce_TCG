<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'code'         => $this->code,
            'release_date' => $this->release_date?->toDateString(),
            'image_url'    => $this->image_url,
            'cards_count'  => $this->whenCounted('cards'),
            'game'         => GameResource::make($this->whenLoaded('game')),
        ];
    }
}
