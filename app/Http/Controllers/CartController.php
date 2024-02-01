<?php

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cart = $user->cart;

        return response()->json(['cart' => $cart], 200);
    }

    public function addToCart(Request $request, Product $product)
    {
        $user = auth()->user();

        $existingCartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingCartItem) {
            $existingCartItem->update([
                'quantity' => $existingCartItem->quantity + 1,
            ]);
        } else {
            Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }

        return response()->json(['message' => 'Product added to cart successfully'], 201);
    }

    public function updateCartItemQuantity(Request $request, Cart $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->update([
            'quantity' => $request->input('quantity'),
        ]);

        return response()->json(['message' => 'Cart item quantity updated successfully'], 200);
    }

    public function removeFromCart(Cart $cartItem)
    {
        $cartItem->delete();

        return response()->json(['message' => 'Product removed from cart successfully'], 200);
    }

    public function clearCart()
    {
        $user = auth()->user();
        $user->cart()->delete();

        return response()->json(['message' => 'Cart cleared successfully'], 200);
    }
}
