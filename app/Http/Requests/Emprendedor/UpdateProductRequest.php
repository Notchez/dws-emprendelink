<?php
namespace App\Http\Requests\Emprendedor;
class UpdateProductRequest extends StoreProductRequest
{
    public function authorize(): bool { return $this->user()?->can('update',$this->route('producto')) === true; }
}
