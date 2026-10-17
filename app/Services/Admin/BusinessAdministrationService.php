<?php

namespace App\Services\Admin;

use App\Models\Emprendimiento;
use App\Models\Plan;
use App\Services\BusinessSlugService;
use Illuminate\Support\Facades\DB;

class BusinessAdministrationService
{
    public function __construct(private BusinessSlugService $slugs) {}

    public function update(Emprendimiento $business, array $data): Emprendimiento
    {
        if ($business->nombre !== $data['nombre']) {
            $data['slug'] = $this->slugs->generate($data['nombre'], $business->id, $business->id);
        }
        $business->update($data);

        return $business->refresh();
    }

    public function assignPlan(Emprendimiento $business, Plan $plan): void
    {
        DB::transaction(function () use ($business, $plan) {
            $business = Emprendimiento::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $current = $business->suscripcionActiva()->first();
            if ($current?->plan_id === $plan->id) {
                return;
            }
            $business->suscripciones()->where('estado', 'activa')->update(['estado' => 'cancelada', 'fecha_fin' => today()]);
            $business->suscripciones()->create(['plan_id' => $plan->id, 'fecha_inicio' => today(), 'estado' => 'activa']);
        });
    }
}
