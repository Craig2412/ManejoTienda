<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $table = Table::where('estado', 'ocupada')->first();

        if (! $table) {
            return;
        }

        Order::updateOrCreate(
            ['table_id' => $table->id, 'estado' => 'abierta'],
            [
                'user_id' => $user?->id,
                'total' => 0,
            ]
        );
    }
}
