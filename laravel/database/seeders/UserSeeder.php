<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cafe.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'barista@cafe.test'],
            [
                'name' => 'Barista',
                'password' => Hash::make('password'),
                'role' => 'barista',
            ]
        );

        User::updateOrCreate(
            ['email' => 'mesero@cafe.test'],
            [
                'name' => 'Mesero',
                'password' => Hash::make('password'),
                'role' => 'mesero',
            ]
        );
    }
}
