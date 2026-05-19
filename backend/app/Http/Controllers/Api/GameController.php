<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\SetResource;
use App\Models\Game;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::withCount('sets')->where('is_active', true)->get();
        return GameResource::collection($games);
    }

    public function show(Game $game)
    {
        $game->load('sets');
        return GameResource::make($game)->additional([
            'sets' => SetResource::collection($game->sets),
        ]);
    }
}
