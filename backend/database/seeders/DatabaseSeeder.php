<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,   // usuarios de prueba
            GameSeeder::class,   // MTG y Pokémon
            MtgSeeder::class,    // sets y cartas de MTG (Scryfall API)
            PokemonSeeder::class, // sets y cartas de Pokémon (PokéTCG API)
        ]);
    }
}
