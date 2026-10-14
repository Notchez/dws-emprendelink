<?php

namespace App\Http\Requests\Emprendedor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();

        if ($usuario === null || $usuario->emprendimiento === null) {
            return false;
        }

        return $usuario->can('update', $usuario->emprendimiento);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}