<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'total'          => $this->total,
            'payment_method' => $this->payment_method,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at->toDateTimeString(),
            'address'        => $this->whenLoaded('address'),
            'items'          => $this->whenLoaded('items', fn() =>
                $this->items->map(fn($item) => [
                    'id'         => $item->id,
                    'card_name'  => $item->card_name,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal'   => $item->subtotal(),
                ])
            ),
        ];
    }
}
