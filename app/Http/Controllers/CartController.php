<?php
// app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends ApiController
{
    public function index(Request $request)
    {
        try {
            $cartItems = Cart::with('product')
                ->where('user_id', $request->user()->id)
                ->get();

            $total = $cartItems->sum(function ($item) {
                return $item->subtotal;
            });

            return $this->success([
                'cart_items' => $cartItems,
                'total' => $total
            ], 'Cart retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve cart: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $product = Product::findOrFail($validated['product_id']);

            // Check stock availability
            if ($product->stock < $validated['quantity']) {
                return $this->error('Insufficient stock. Available: ' . $product->stock, 422);
            }

            // Check if product already in cart
            $existingCartItem = Cart::where('user_id', $request->user()->id)
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($existingCartItem) {
                // Update quantity if already exists
                $existingCartItem->quantity += $validated['quantity'];
                $existingCartItem->save();
                $cartItem = $existingCartItem;
            } else {
                // Create new cart item
                $cartItem = Cart::create([
                    'user_id' => $request->user()->id,
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                ]);
            }

            return $this->success($cartItem->load('product'), 'Product added to cart successfully', 201);

        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Product not found');
        } catch (\Exception $e) {
            return $this->error('Failed to add product to cart: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = Cart::where('user_id', $request->user()->id)
                ->findOrFail($id);

            $product = $cartItem->product;

            // Check stock availability
            if ($product->stock < $validated['quantity']) {
                return $this->error('Insufficient stock. Available: ' . $product->stock, 422);
            }

            $cartItem->update(['quantity' => $validated['quantity']]);

            return $this->success($cartItem->load('product'), 'Cart updated successfully');

        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Cart item not found');
        } catch (\Exception $e) {
            return $this->error('Failed to update cart: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $cartItem = Cart::where('user_id', $request->user()->id)
                ->findOrFail($id);

            $cartItem->delete();

            return $this->success(null, 'Item removed from cart successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Cart item not found');
        } catch (\Exception $e) {
            return $this->error('Failed to remove item from cart: ' . $e->getMessage(), 500);
        }
    }
}
