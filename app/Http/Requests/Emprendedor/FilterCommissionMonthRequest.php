<?php

namespace App\Http\Requests\Emprendedor;

use App\Models\Comision;
use Illuminate\Foundation\Http\FormRequest;

class FilterCommissionMonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Comision::class) === true;
    }

    public function rules(): array
    {
        return ['mes' => ['nullable', 'date_format:Y-m']];
    }
}
