<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CardResource;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = Card::with('set.game')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->set_id, fn($q) => $q->where('set_id', $request->set_id))
            ->when($request->rarity, fn($q) => $q->where('rarity', $request->rarity))
            ->when($request->game, fn($q) => $q->whereHas('set.game', fn($q2) => $q2->where('slug', $request->game)))
            ->orderBy('name')
            ->paginate(24);

        return CardResource::collection($cards);
    }

    public function show(Card $card)
    {
        // Carga los productos activos con el vendedor — listo para mostrar en la página de la carta
        $card->load(['set.game', 'products' => fn($q) => $q->active()->with('seller')]);
        return CardResource::make($card);
    }
}
