<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSeller() ?? false;
    }

    public function rules(): array
    {
        return [
            'card_id'   => ['required', 'integer', 'exists:cards,id'],
            'condition' => ['required', 'in:mint,near_mint,lightly_played,moderately_played,heavily_played,damaged'],
            'language'  => ['required', 'string', 'max:10'],
            'price'     => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'stock'     => ['required', 'integer', 'min:1', 'max:9999'],
            'is_foil'   => ['sometimes', 'boolean'],
        ];
    }
}
