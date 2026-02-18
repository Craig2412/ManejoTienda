<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'nombre' => 'Cafe',
                'descripcion' => 'Bebidas a base de cafe y espresso.',
            ],
            [
                'nombre' => 'Te',
                'descripcion' => 'Infusiones calientes y frias.',
            ],
            [
                'nombre' => 'Reposteria',
                'descripcion' => 'Postres, pasteles y galletas.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['nombre' => $category['nombre']],
                $category
            );
        }
    }
}
