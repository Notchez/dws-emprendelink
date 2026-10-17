<?php
namespace App\Services\Emprendedor;
use App\Exceptions\ProductLimitReachedException;
use App\Models\Emprendimiento;
use App\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProductCatalogService
{
    public function create(Emprendimiento $business, array $data, ?UploadedFile $image = null): Producto
    {
        $storedImage = null;

        try {
            return DB::transaction(function () use ($business, $data, $image, &$storedImage) {
                $locked = Emprendimiento::whereKey($business->id)->lockForUpdate()->firstOrFail();

                if ($data['estado'] ?? true) {
                    $this->ensureCapacity($locked);
                }

                if ($image) {
                    $storedImage = Storage::disk('public')->putFile('productos', $image);

                    if (! $storedImage) {
                        throw new RuntimeException('No se pudo guardar la imagen del producto.');
                    }

                    $data['imagen_url'] = $storedImage;
                }

                return $locked->productos()->create($data);
            });
        } catch (Throwable $exception) {
            if ($storedImage) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $exception;
        }
    }

    public function update(Producto $product, array $data, ?UploadedFile $image = null): Producto
    {
        $previousImage = null;
        $storedImage = null;

        try {
            $updated = DB::transaction(function () use ($product, $data, $image, &$previousImage, &$storedImage) {
                $product = Producto::whereKey($product->id)->lockForUpdate()->firstOrFail();
                $previousImage = $product->imagen_url;

                if (! $product->estado && ($data['estado'] ?? false)) {
                    $this->ensureCapacity($product->emprendimiento);
                }

                if ($image) {
                    $storedImage = Storage::disk('public')->putFile('productos', $image);

                    if (! $storedImage) {
                        throw new RuntimeException('No se pudo guardar la imagen del producto.');
                    }

                    $data['imagen_url'] = $storedImage;
                }

                $product->update($data);

                return $product->refresh();
            });
        } catch (Throwable $exception) {
            if ($storedImage) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $exception;
        }

        if ($storedImage && $previousImage && $previousImage !== $storedImage) {
            Storage::disk('public')->delete($previousImage);
        }

        return $updated;
    }

    public function setStatus(Producto $product, bool $active): Producto
    {
        return $this->update($product, ['estado' => $active]);
    }

    private function ensureCapacity(Emprendimiento $business): void
    {
        $limit=$business->suscripcionActiva()->with('plan')->first()?->plan?->limite_productos;
        if ($limit === null || $business->productos()->where('estado',true)->count() >= $limit) {
            throw new ProductLimitReachedException('Has alcanzado el límite de productos activos de tu plan.');
        }
    }
}
