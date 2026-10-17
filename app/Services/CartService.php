<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Collection;

class CartService
{
    private const KEY = 'cart';

    public function add(Producto $product, int $quantity = 1): void
    {
        $product->loadMissing(['categoria', 'emprendimiento.usuario']);

        abort_unless(
            $product->estado
                && ($product->categoria === null || $product->categoria->estado)
                && $product->emprendimiento?->estado
                && $product->emprendimiento?->usuario?->estado,
            422,
            'El producto no está disponible.',
        );

        $cart = session(self::KEY, []);
        $cart[$product->id] = min(99, ($cart[$product->id] ?? 0) + $quantity);
        session([self::KEY => $cart]);
    }

    public function update(Producto $product, int $quantity): void
    {
        $cart = session(self::KEY, []);
        abort_unless(array_key_exists($product->id, $cart), 404);

        if ($quantity < 1) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = min(99, $quantity);
        }

        session([self::KEY => $cart]);
    }

    public function remove(Producto $product): void
    {
        $cart = session(self::KEY, []);
        unset($cart[$product->id]);
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function items(): Collection
    {
        $cart = session(self::KEY, []);

        if ($cart === []) {
            return collect();
        }

        $products = Producto::query()
            ->with(['emprendimiento.usuario', 'categoria'])
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(function ($quantity, $id) use ($products): ?array {
                $product = $products->get($id);

                if (! $product) {
                    return null;
                }

                return [
                    'product' => $product,
                    'quantity' => (int) $quantity,
                    'subtotal' => (float) $product->precio * (int) $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function total(): float
    {
        return $this->items()->sum('subtotal');
    }
}
