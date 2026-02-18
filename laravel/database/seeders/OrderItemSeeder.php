<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = Order::where('estado', 'abierta')->first();
        $products = Product::take(2)->get();

        if (! $order || $products->isEmpty()) {
            return;
        }

        $total = 0;

        foreach ($products as $product) {
            $cantidad = 2;
            $subtotal = $cantidad * $product->precio;
            $total += $subtotal;

            $order->items()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'cantidad' => $cantidad,
                    'precio_unitario' => $product->precio,
                    'subtotal' => $subtotal,
                ]
            );
        }

        $order->update(['total' => $total]);
    }
}
