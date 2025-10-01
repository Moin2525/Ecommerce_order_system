<?php
// app/Traits/CommonQueryScopes.php

namespace App\Traits;

trait CommonQueryScopes
{
    /**
     * Filter by price range
     */
    public function scopeFilterByPrice($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Search by name
     */
    public function scopeSearchByName($query, $searchTerm)
    {
        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        return $query;
    }

    /**
     * Filter by category
     */
    public function scopeFilterByCategory($query, $categoryId)
    {
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Order by latest
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Order by price
     */
    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    /**
     * Get items in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Get out of stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Get popular items (by stock threshold)
     */
    public function scopePopular($query, $threshold = 10)
    {
        return $query->where('stock', '<=', $threshold);
    }
}
