<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // cualquiera puede registrarse
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['sometimes', 'in:buyer,seller'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'         => 'Este email ya está registrado.',
            'password.confirmed'   => 'Las contraseñas no coinciden.',
            'password.min'         => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
