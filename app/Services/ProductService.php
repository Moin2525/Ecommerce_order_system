<?php
// app/Services/ProductService.php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    protected $cacheTime = 3600; // 1 hour

    public function getCachedProducts($filters = [])
    {
        $cacheKey = $this->generateCacheKey($filters);

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($filters) {
            $query = Product::with('category');

            // Apply filters
            if (isset($filters['search'])) {
                $query->searchByName($filters['search']);
            }

            if (isset($filters['category_id'])) {
                $query->filterByCategory($filters['category_id']);
            }

            if (isset($filters['min_price']) || isset($filters['max_price'])) {
                $query->filterByPrice($filters['min_price'] ?? null, $filters['max_price'] ?? null);
            }

            if (isset($filters['in_stock']) && $filters['in_stock']) {
                $query->inStock();
            }

            // Ordering
            $sortBy = $filters['sort_by'] ?? 'latest';
            switch ($sortBy) {
                case 'price_asc':
                    $query->orderByPrice('asc');
                    break;
                case 'price_desc':
                    $query->orderByPrice('desc');
                    break;
                case 'latest':
                default:
                    $query->latestFirst();
            }

            return $query->get();
        });
    }

    public function getCachedProduct($id)
    {
        $cacheKey = "product_{$id}";

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
            return Product::with('category')->find($id);
        });
    }

    public function clearProductCache($id = null)
    {
        if ($id) {
            Cache::forget("product_{$id}");
        }

        // Clear all product list caches (simplified approach)
        Cache::flush();
    }

    protected function generateCacheKey($filters)
    {
        ksort($filters); // Sort filters for consistent cache keys
        return 'products_' . md5(serialize($filters));
    }
}
