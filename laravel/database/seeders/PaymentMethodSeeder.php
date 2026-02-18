<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['nombre' => 'Efectivo', 'requiere_referencia' => false],
            ['nombre' => 'Transferencia', 'requiere_referencia' => true],
            ['nombre' => 'Pago movil', 'requiere_referencia' => true],
            ['nombre' => 'Criptomoneda', 'requiere_referencia' => true],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['nombre' => $method['nombre']],
                [
                    'requiere_referencia' => $method['requiere_referencia'],
                    'activo' => true,
                ]
            );
        }
    }
}
