<?php
namespace App\Services\Admin;
use App\Models\Plan;
class PlanManagementService
{
    public function create(array $data): Plan { return Plan::create($data); }
    public function update(Plan $plan,array $data): Plan { $plan->update($data); return $plan->refresh(); }
}
