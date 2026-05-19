<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items', 'address'])
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order)
    {
        // Solo el dueño puede ver su pedido
        abort_if($order->user_id !== $request->user()->id, Response::HTTP_FORBIDDEN);
        $order->load(['items', 'address']);
        return OrderResource::make($order);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'address_id'     => 'required|exists:addresses,id',
            'payment_method' => 'required|string|max:50',
            'notes'          => 'nullable|string|max:500',
        ]);

        $cart = $request->user()->cart()->with('items.product.card')->first();

        abort_if(! $cart || $cart->items->isEmpty(), Response::HTTP_UNPROCESSABLE_ENTITY, 'El carrito está vacío.');

        // DB::transaction asegura que si algo falla a mitad, nada se guarda (atomicidad)
        $order = DB::transaction(function () use ($request, $data, $cart) {
            $total = $cart->items->sum(fn($i) => $i->product->price * $i->quantity);

            $order = $request->user()->orders()->create([
                'address_id'     => $data['address_id'],
                'payment_method' => $data['payment_method'],
                'notes'          => $data['notes'] ?? null,
                'total'          => $total,
                'status'         => 'pending',
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'card_name'  => $item->product->card->name,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->product->price,
                ]);

                // Descuenta el stock al confirmar el pedido
                $item->product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return $order;
        });

        return OrderResource::make($order->load(['items', 'address']))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
