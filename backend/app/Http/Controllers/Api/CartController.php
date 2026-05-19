<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id]);
        }

        // Para usuarios no registrados, el carrito se identifica por session_id
        $sessionId = $request->header('X-Session-Id') ?? $request->session_id;
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function show(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load(['items.product.card.set']);

        return response()->json([
            'items' => $cart->items->map(fn($item) => [
                'id'       => $item->id,
                'quantity' => $item->quantity,
                'product'  => [
                    'id'        => $item->product->id,
                    'price'     => $item->product->price,
                    'condition' => $item->product->condition,
                    'is_foil'   => $item->product->is_foil,
                    'card'      => [
                        'name'      => $item->product->card->name,
                        'image_url' => $item->product->card->image_url,
                        'set'       => $item->product->card->set->name,
                    ],
                ],
                'subtotal' => $item->product->price * $item->quantity,
            ]),
            'total' => $cart->total(),
        ]);
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'integer|min:1|max:99',
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        $cart    = $this->getCart($request);

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $newQty = min($item->quantity + ($data['quantity'] ?? 1), $product->stock);
            $item->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity'   => min($data['quantity'] ?? 1, $product->stock),
            ]);
        }

        return $this->show($request);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:99']);
        $cart = $this->getCart($request);

        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => min($data['quantity'], $item->product->stock)]);

        return $this->show($request);
    }

    public function removeItem(Request $request, int $itemId)
    {
        $cart = $this->getCart($request);
        $cart->items()->findOrFail($itemId)->delete();
        return $this->show($request);
    }

    public function clear(Request $request)
    {
        $this->getCart($request)->items()->delete();
        return response()->noContent();
    }
}
