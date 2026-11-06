<?php

namespace App\Http\Requests\Emprendedor;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Services\Reports\ReportFilters;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FilterEntrepreneurReportRequest extends FormRequest
{
    protected $redirectRoute = 'entrepreneur.reports.index';

    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::EMPRENDEDOR) === true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'desde' => ['bail', 'nullable', 'string', 'date_format:Y-m-d'],
            'hasta' => ['bail', 'nullable', 'string', 'date_format:Y-m-d'],
            'estado' => ['bail', 'nullable', Rule::enum(OrderStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'desde' => 'fecha inicial',
            'hasta' => 'fecha final',
            'estado' => 'estado del pedido',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'desde.string' => 'La fecha inicial no tiene un formato válido.',
            'desde.date_format' => 'La fecha inicial debe tener el formato AAAA-MM-DD.',
            'hasta.string' => 'La fecha final no tiene un formato válido.',
            'hasta.date_format' => 'La fecha final debe tener el formato AAAA-MM-DD.',
            'estado.enum' => 'Selecciona un estado de pedido válido.',
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
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

                $startDate = CarbonImmutable::createFromFormat('!Y-m-d', $desde);
                $endDate = CarbonImmutable::createFromFormat('!Y-m-d', $hasta);

                if ($startDate->gt($endDate)) {
                    $validator->errors()->add(
                        'hasta',
                        'La fecha inicial no puede ser posterior a la final.',
                    );
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

    public function orderStatus(): ?OrderStatus
    {
        $value = $this->validated('estado');

        return is_string($value) && $value !== '' ? OrderStatus::from($value) : null;
    }
}
