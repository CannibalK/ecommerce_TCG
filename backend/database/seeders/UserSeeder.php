<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@tcg.local'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'seller@tcg.local'],
            [
                'name'     => 'Seller Demo',
                'password' => Hash::make('password'),
                'role'     => 'seller',
            ]
        );

        User::firstOrCreate(
            ['email' => 'buyer@tcg.local'],
            [
                'name'     => 'Buyer Demo',
                'password' => Hash::make('password'),
                'role'     => 'buyer',
            ]
        );
    }
}
