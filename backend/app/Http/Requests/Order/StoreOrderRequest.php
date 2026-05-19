<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados pueden crear órdenes
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id'     => ['required', 'integer', 'exists:addresses,id'],
            'payment_method' => ['required', 'string', 'max:50'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }
}
