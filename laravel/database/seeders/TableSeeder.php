<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Table::updateOrCreate(
                ['numero' => $i],
                [
                    'capacidad' => $i <= 4 ? 2 : 4,
                    'estado' => $i % 3 === 0 ? 'ocupada' : 'disponible',
                ]
            );
        }
    }
}
