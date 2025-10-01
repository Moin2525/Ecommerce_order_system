<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends ApiController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'category_id', 'min_price', 'max_price', 'in_stock', 'sort_by']);

            $products = $this->productService->getCachedProducts($filters);

            return $this->success($products, 'Products retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve products: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $product = $this->productService->getCachedProduct($id);

            if (!$product) {
                return $this->notFound('Product not found');
            }

            return $this->success($product, 'Product retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve product: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
            ]);

            $product = Product::create($validated);

            // Clear cache when new product is added
            $this->productService->clearProductCache();

            return $this->success($product, 'Product created successfully', 201);

        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->error('Failed to create product: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
            ]);

            $product->update($validated);

            // Clear cache when product is updated
            $this->productService->clearProductCache($id);

            return $this->success($product, 'Product updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Product not found');
        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->error('Failed to update product: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            // Check if product is in any cart or order
            if ($product->carts()->exists() || $product->orderItems()->exists()) {
                return $this->error('Cannot delete product with existing cart items or order history', 422);
            }

            $product->delete();

            // Clear cache when product is deleted
            $this->productService->clearProductCache($id);

            return $this->success(null, 'Product deleted successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Product not found');
        } catch (\Exception $e) {
            return $this->error('Failed to delete product: ' . $e->getMessage(), 500);
        }
    }
}
