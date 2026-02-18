<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cafe = Category::where('nombre', 'Cafe')->first();
        $te = Category::where('nombre', 'Te')->first();
        $reposteria = Category::where('nombre', 'Reposteria')->first();

        if (! $cafe || ! $te || ! $reposteria) {
            return;
        }

        $products = [
            [
                'category_id' => $cafe->id,
                'nombre' => 'Espresso',
                'descripcion' => 'Shot de espresso clasico.',
                'precio' => 2.50,
                'stock_actual' => 50,
                'stock_minimo' => 10,
                'estado' => 'activo',
            ],
            [
                'category_id' => $cafe->id,
                'nombre' => 'Latte Vainilla',
                'descripcion' => 'Cafe con leche y vainilla.',
                'precio' => 3.75,
                'stock_actual' => 18,
                'stock_minimo' => 15,
                'estado' => 'activo',
            ],
            [
                'category_id' => $te->id,
                'nombre' => 'Te Verde',
                'descripcion' => 'Infusion de te verde.',
                'precio' => 2.25,
                'stock_actual' => 8,
                'stock_minimo' => 12,
                'estado' => 'activo',
            ],
            [
                'category_id' => $reposteria->id,
                'nombre' => 'Croissant',
                'descripcion' => 'Croissant horneado diariamente.',
                'precio' => 2.10,
                'stock_actual' => 22,
                'stock_minimo' => 8,
                'estado' => 'activo',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['nombre' => $product['nombre']],
                $product
            );
        }
    }
}
