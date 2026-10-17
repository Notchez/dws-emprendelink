<?php

namespace App\Services\Commissions;

use App\Enums\CommissionStatus;
use App\Models\Comision;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommissionStatusService
{
    public function transition(Comision $commission, CommissionStatus $next): Comision
    {
        return DB::transaction(function () use ($commission, $next) {
            $commission = Comision::whereKey($commission->id)->lockForUpdate()->firstOrFail();

            if ($commission->estado !== CommissionStatus::PENDIENTE) {
                throw ValidationException::withMessages([
                    'estado' => 'Una comisión pagada o anulada no puede cambiar de estado.',
                ]);
            }

            if (! in_array($next, [CommissionStatus::PAGADA, CommissionStatus::ANULADA], true)) {
                throw ValidationException::withMessages([
                    'estado' => 'La transición solicitada no está permitida.',
                ]);
            }

            $commission->update([
                'estado' => $next,
                'pagada_at' => $next === CommissionStatus::PAGADA ? now() : null,
            ]);

            return $commission->refresh();
        });
    }
}
