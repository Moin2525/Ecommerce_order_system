<?php
// app/Http/Middleware/CheckStockMiddleware.php

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStockMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for order creation
        if ($request->route()->getName() === 'orders.store') {
            $user = $request->user();
            $cartItems = Cart::with('product')
                ->where('user_id', $user->id)
                ->get();

            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$item->product->name}. Available: {$item->product->stock}, Requested: {$item->quantity}"
                    ], 422);
                }
            }
        }

        return $next($request);
    }
}
