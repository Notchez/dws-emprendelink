<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PayAccountStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pay', $this->route('estadoCuenta')) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
