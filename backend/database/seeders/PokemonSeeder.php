<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Game;
use App\Models\Set;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PokemonSeeder extends Seeder
{
    // PokéTCG API — gratuita, sin key (con key tiene más rate limit)
    private const POKEMON_API_URL = 'https://api.pokemontcg.io/v2';

    // Sets que importamos (IDs de la PokéTCG API)
    private const SETS_TO_IMPORT = ['sv1', 'sv2', 'sv3'];

    public function run(): void
    {
        $game = Game::where('slug', 'pokemon')->firstOrFail();

        $this->command->info('Importando sets de Pokémon TCG...');

        foreach (self::SETS_TO_IMPORT as $setId) {
            $this->importSet($game, $setId);
            usleep(200_000);
        }
    }

    private function importSet(Game $game, string $setId): void
    {
        $response = Http::get(self::POKEMON_API_URL . "/sets/{$setId}");

        if ($response->failed()) {
            $this->command->warn("Set {$setId} no encontrado.");
            return;
        }

        $setData = $response->json()['data'];

        $set = Set::firstOrCreate(
            ['code' => strtoupper($setId)],
            [
                'game_id'      => $game->id,
                'name'         => $setData['name'],
                'release_date' => $setData['releaseDate'] ?? null,
                'image_url'    => $setData['images']['logo'] ?? null,
            ]
        );

        $this->command->info("  Set: {$set->name} — importando cartas...");
        $this->importCards($set);
    }

    private function importCards(Set $set): void
    {
        $page     = 1;
        $pageSize = 250;

        do {
            $response = Http::get(self::POKEMON_API_URL . '/cards', [
                'q'        => "set.id:{$set->code}",
                'page'     => $page,
                'pageSize' => $pageSize,
                'orderBy'  => 'number',
            ]);

            if ($response->failed()) break;

            $data  = $response->json();
            $cards = $data['data'] ?? [];

            foreach ($cards as $cardData) {
                Card::firstOrCreate(
                    [
                        'set_id'           => $set->id,
                        'collector_number' => $cardData['number'],
                    ],
                    [
                        'name'      => $cardData['name'],
                        'slug'      => Str::slug($cardData['name'] . '-' . $cardData['number']),
                        'rarity'    => $this->mapRarity($cardData['rarity'] ?? 'common'),
                        'image_url' => $cardData['images']['large'] ?? $cardData['images']['small'] ?? null,
                        // Atributos Pokémon-específicos
                        'attributes' => [
                            'supertype' => $cardData['supertype'] ?? null,
                            'subtypes'  => $cardData['subtypes'] ?? [],
                            'hp'        => $cardData['hp'] ?? null,
                            'types'     => $cardData['types'] ?? [],
                            'attacks'   => collect($cardData['attacks'] ?? [])->map(fn($a) => [
                                'name'   => $a['name'],
                                'damage' => $a['damage'],
                                'cost'   => $a['cost'],
                            ])->toArray(),
                        ],
                    ]
                );
            }

            $this->command->info("    Página {$page}: " . count($cards) . " cartas");
            $totalCount = $data['totalCount'] ?? 0;
            $page++;
            usleep(200_000);

        } while (count($cards) === $pageSize && ($page - 1) * $pageSize < $totalCount);
    }

    private function mapRarity(string $rarity): string
    {
        $rarity = strtolower($rarity);

        if (str_contains($rarity, 'rare holo')) return 'rare';
        if (str_contains($rarity, 'rare')) return 'rare';
        if (str_contains($rarity, 'uncommon')) return 'uncommon';
        if (str_contains($rarity, 'illustration rare') || str_contains($rarity, 'special')) return 'special';

        return 'common';
    }
}
