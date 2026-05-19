<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
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

    public function store(StoreProductRequest $request)
    {
        $product = $request->user()->listings()->create($request->validated());

        return ProductResource::make($product->load('card'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $product->update($request->validated());
        return ProductResource::make($product->load('card'));
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return response()->noContent();
    }
}
