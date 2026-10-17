<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['email' => ['required', 'email'], 'password' => ['required', 'string'], 'remember' => ['nullable', 'boolean']];
    }

    public function messages(): array
    {
        return ['email.required' => 'Ingresa tu correo electrónico.', 'email.email' => 'Ingresa un correo válido.', 'password.required' => 'Ingresa tu contraseña.'];
    }
}
