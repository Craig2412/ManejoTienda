<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['nombre' => 'Dolares', 'codigo' => 'USD', 'simbolo' => '$'],
            ['nombre' => 'Bolivares', 'codigo' => 'VES', 'simbolo' => 'Bs'],
            ['nombre' => 'Euros', 'codigo' => 'EUR', 'simbolo' => 'EUR'],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['codigo' => $currency['codigo']],
                [
                    'nombre' => $currency['nombre'],
                    'simbolo' => $currency['simbolo'],
                    'activo' => true,
                ]
            );
        }
    }
}
