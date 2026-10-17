<?php

namespace App\Http\Requests\Admin;

use App\Enums\CommissionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommissionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('comision')) === true;
    }

    public function rules(): array
    {
        return [
            'estado' => [
                'required',
                Rule::in([
                    CommissionStatus::PAGADA->value,
                    CommissionStatus::ANULADA->value,
                ]),
            ],
        ];
    }
}
