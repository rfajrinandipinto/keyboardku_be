<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;


class OrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('orderItems.product')->get();

        return response()->json(['orders' => $orders], 200);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'total_price' => 'required|numeric|min:0',
            'address' => 'nullable|string',
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();

        $order = $user->orders()->create([
            'total_price' => $request->input('total_price'),
            'status' => 'pending',
            'address' => $request->input('address'),
        ]);

        foreach ($request->input('order_items') as $item) {
            $product = $item['product_id'];
            $quantity = $item['quantity'];

            $subtotal = $product->price * $quantity;

            $orderItem = new OrderItem([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ]);

            $order->orderItems()->save($orderItem);
        }

        return response()->json(['message' => 'Order placed successfully'], 201);
    }
}
