<?php
// app/Traits/DiscountTrait.php

namespace App\Traits;

trait DiscountTrait
{
    /**
     * Calculate discount amount
     */
    public function calculateDiscount($amount, $discountPercentage)
    {
        return $amount * ($discountPercentage / 100);
    }

    /**
     * Apply discount to amount
     */
    public function applyDiscount($amount, $discountPercentage)
    {
        $discount = $this->calculateDiscount($amount, $discountPercentage);
        return $amount - $discount;
    }

    /**
     * Check if user is eligible for discount
     */
    public function isEligibleForDiscount($user, $minimumOrderAmount = 100)
    {
        // Example logic: users with more than 5 orders get discount
        $orderCount = $user->orders()->count();
        return $orderCount > 5;
    }

    /**
     * Get discount percentage based on user loyalty
     */
    public function getLoyaltyDiscountPercentage($user)
    {
        $orderCount = $user->orders()->count();

        if ($orderCount >= 10) return 15;
        if ($orderCount >= 5) return 10;
        if ($orderCount >= 2) return 5;

        return 0;
    }
}
