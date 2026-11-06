<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Services\Reports\ReportFilters;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FilterAdminReportRequest extends FormRequest
{
    protected $redirectRoute = 'admin.reports.index';

    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::ADMIN) === true;
    }

    public function rules(): array
    {
        return [
            'desde' => ['bail', 'nullable', 'string', 'date_format:Y-m-d'],
            'hasta' => ['bail', 'nullable', 'string', 'date_format:Y-m-d'],
            'emprendimiento_id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists('emprendimientos', 'id')->whereNull('deleted_at'),
            ],
            'estado' => ['bail', 'nullable', Rule::enum(OrderStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'desde.string' => 'La fecha inicial no tiene un formato válido.',
            'desde.date_format' => 'La fecha inicial debe tener el formato AAAA-MM-DD.',
            'hasta.string' => 'La fecha final no tiene un formato válido.',
            'hasta.date_format' => 'La fecha final debe tener el formato AAAA-MM-DD.',
            'emprendimiento_id.integer' => 'Selecciona un emprendimiento válido.',
            'emprendimiento_id.exists' => 'El emprendimiento seleccionado no existe.',
            'estado.enum' => 'Selecciona un estado de pedido válido.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $desde = $this->input('desde');
                $hasta = $this->input('hasta');

                if (! is_string($desde) || $desde === '' || ! is_string($hasta) || $hasta === '') {
                    return;
                }

                if (CarbonImmutable::createFromFormat('!Y-m-d', $desde)
                    ->gt(CarbonImmutable::createFromFormat('!Y-m-d', $hasta))) {
                    $validator->errors()->add('hasta', 'La fecha inicial no puede ser posterior a la final.');
                }
            },
        ];
    }

    public function filters(): ReportFilters
    {
        $validated = $this->validated();

        return ReportFilters::fromDates(
            $validated['desde'] ?? null,
            $validated['hasta'] ?? null,
        );
    }

    public function businessId(): ?int
    {
        $value = $this->validated('emprendimiento_id');

        return $value === null ? null : (int) $value;
    }

    public function orderStatus(): ?OrderStatus
    {
        $value = $this->validated('estado');

        return is_string($value) && $value !== '' ? OrderStatus::from($value) : null;
    }
}
