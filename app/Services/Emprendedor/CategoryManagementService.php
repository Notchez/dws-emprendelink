<?php
namespace App\Services\Emprendedor;
use App\Models\Categoria;
use App\Models\Emprendimiento;
class CategoryManagementService
{
    public function create(Emprendimiento $business,array $data): Categoria { return $business->categorias()->create($data); }
    public function update(Categoria $category,array $data): Categoria { $category->update($data); return $category->refresh(); }
    public function delete(Categoria $category): void { $category->productos()->update(['categoria_id'=>null]); $category->delete(); }
}
