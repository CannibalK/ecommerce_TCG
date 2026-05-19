<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Game;
use App\Models\Set;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MtgSeeder extends Seeder
{
    // Scryfall es la API pública más completa de MTG — gratuita, sin key
    private const SCRYFALL_SETS_URL  = 'https://api.scryfall.com/sets';
    private const SCRYFALL_CARDS_URL = 'https://api.scryfall.com/cards/search';

    // Scryfall requiere User-Agent y Accept en todas las peticiones
    private function scryfallClient()
    {
        return Http::withHeaders([
            'User-Agent' => 'TCGEcommerce/1.0 (portfolio project)',
            'Accept'     => 'application/json',
        ]);
    }

    // Sets que vamos a importar (códigos de Scryfall)
    private const SETS_TO_IMPORT = ['dsk', 'blb', 'otj'];

    public function run(): void
    {
        $game = Game::where('slug', 'mtg')->firstOrFail();

        $this->command->info('Importando sets de MTG desde Scryfall...');

        foreach (self::SETS_TO_IMPORT as $setCode) {
            $this->importSet($game, $setCode);
            // Scryfall pide respetar 50-100ms entre requests
            usleep(100_000);
        }
    }

    private function importSet(Game $game, string $setCode): void
    {
        $response = $this->scryfallClient()->get(self::SCRYFALL_SETS_URL . "/{$setCode}");

        if ($response->failed()) {
            $this->command->warn("Set {$setCode} no encontrado en Scryfall.");
            return;
        }

        $setData = $response->json();

        $set = Set::firstOrCreate(
            ['code' => strtoupper($setCode)],
            [
                'game_id'      => $game->id,
                'name'         => $setData['name'],
                'release_date' => $setData['released_at'] ?? null,
                'image_url'    => $setData['icon_svg_uri'] ?? null,
            ]
        );

        $this->command->info("  Set: {$set->name} — importando cartas...");
        $this->importCards($set);
    }

    private function importCards(Set $set): void
    {
        $url  = self::SCRYFALL_CARDS_URL;
        $page = 1;

        do {
            $response = $this->scryfallClient()->get($url, [
                'q'       => "set:{$set->code} game:paper",
                'page'    => $page,
                'order'   => 'collector_number',
            ]);

            if ($response->failed()) break;

            $data  = $response->json();
            $cards = $data['data'] ?? [];

            foreach ($cards as $cardData) {
                // Algunas cartas tienen dos caras — usamos la imagen del frente
                $imageUrl = $cardData['image_uris']['normal']
                    ?? $cardData['card_faces'][0]['image_uris']['normal']
                    ?? null;

                Card::firstOrCreate(
                    [
                        'set_id'           => $set->id,
                        'collector_number' => $cardData['collector_number'],
                    ],
                    [
                        'name'   => $cardData['name'],
                        'slug'   => Str::slug($cardData['name']),
                        'rarity' => $this->mapRarity($cardData['rarity']),
                        'image_url'  => $imageUrl,
                        // Guardamos atributos MTG-específicos en JSON
                        'attributes' => [
                            'mana_cost'  => $cardData['mana_cost'] ?? null,
                            'type_line'  => $cardData['type_line'] ?? null,
                            'colors'     => $cardData['colors'] ?? [],
                            'oracle_text'=> $cardData['oracle_text'] ?? null,
                            'power'      => $cardData['power'] ?? null,
                            'toughness'  => $cardData['toughness'] ?? null,
                        ],
                    ]
                );
            }

            $this->command->info("    Página {$page}: " . count($cards) . " cartas");
            $hasMore = $data['has_more'] ?? false;
            $page++;
            usleep(100_000);

        } while ($hasMore);
    }

    private function mapRarity(string $rarity): string
    {
        return match ($rarity) {
            'mythic' => 'mythic',
            'rare'   => 'rare',
            'uncommon' => 'uncommon',
            default  => 'common',
        };
    }
}
