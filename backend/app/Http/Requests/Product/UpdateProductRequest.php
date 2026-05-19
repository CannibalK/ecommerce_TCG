<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización real (dueño o admin) la maneja la Policy en el controller
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'condition' => ['sometimes', 'in:mint,near_mint,lightly_played,moderately_played,heavily_played,damaged'],
            'price'     => ['sometimes', 'numeric', 'min:0.01', 'max:99999.99'],
            'stock'     => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
