<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            ['name' => 'Magic: The Gathering', 'slug' => 'mtg'],
            ['name' => 'Pokémon TCG',          'slug' => 'pokemon'],
        ];

        foreach ($games as $game) {
            Game::firstOrCreate(['slug' => $game['slug']], $game);
        }
    }
}
