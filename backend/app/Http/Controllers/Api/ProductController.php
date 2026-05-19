<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()
            ->with(['card.set.game', 'seller'])
            ->when($request->condition, fn($q) => $q->where('condition', $request->condition))
            ->when($request->is_foil, fn($q) => $q->where('is_foil', filter_var($request->is_foil, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->game, fn($q) => $q->whereHas('card.set.game', fn($q2) => $q2->where('slug', $request->game)))
            ->orderBy('price')
            ->paginate(24);

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $product->load(['card.set.game', 'seller']);
        return ProductResource::make($product);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'card_id'   => 'required|exists:cards,id',
            'condition' => 'required|in:mint,near_mint,lightly_played,moderately_played,heavily_played,damaged',
            'language'  => 'required|string|max:10',
            'price'     => 'required|numeric|min:0.01',
            'stock'     => 'required|integer|min:1',
            'is_foil'   => 'boolean',
        ]);

        $product = $request->user()->listings()->create($data);

        return ProductResource::make($product->load('card'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'condition' => 'in:mint,near_mint,lightly_played,moderately_played,heavily_played,damaged',
            'price'     => 'numeric|min:0.01',
            'stock'     => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($data);

        return ProductResource::make($product->load('card'));
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return response()->noContent();
    }
}
