<?php

namespace App\Http\Requests\Emprendedor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
{
    $emprendimiento = $this->route('emprendimiento');

    return $emprendimiento instanceof \App\Models\Emprendimiento
        && $this->user()?->can('update', $emprendimiento) === true;
}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'nombre' => ['required', 'string', 'max:150'],
        'descripcion' => ['nullable', 'string'],
        'telefono_contacto' => ['nullable', 'string', 'max:20'],
        'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ];
}
}
