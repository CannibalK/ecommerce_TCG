<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetResource;
use App\Models\Set;
use Illuminate\Http\Request;

class SetController extends Controller
{
    public function index(Request $request)
    {
        $sets = Set::withCount('cards')
            ->with('game')
            ->when($request->game_id, fn($q) => $q->where('game_id', $request->game_id))
            ->when($request->game, fn($q) => $q->whereHas('game', fn($q2) => $q2->where('slug', $request->game)))
            ->orderBy('release_date', 'desc')
            ->get();

        return SetResource::collection($sets);
    }

    public function show(Set $set)
    {
        $set->loadCount('cards')->load('game');
        return SetResource::make($set);
    }
}
