<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    /**
     * Toggle a product in the user's wishlist.
     *
     * @return array{is_wishlisted: bool, wishlist: Wishlist|null}
     */
    public function toggle(int $userId, int $productId): array
    {
        $product = Product::active()->findOrFail($productId);

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();

            return [
                'is_wishlisted' => false,
                'wishlist'      => null,
            ];
        }

        $wishlist = Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return [
            'is_wishlisted' => true,
            'wishlist'      => $wishlist,
        ];
    }

    /**
     * List all wishlist items for a user.
     */
    public function list(int $userId)
    {
        return Wishlist::where('user_id', $userId)
            ->with(['product' => function ($query) {
                $query->with(['category', 'images']);
            }])
            ->latest()
            ->get();
    }

    /**
     * Remove a specific item from the wishlist.
     */
    public function remove(int $userId, int $productId): bool
    {
        return Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Clear the entire wishlist for a user.
     */
    public function clear(int $userId): void
    {
        Wishlist::where('user_id', $userId)->delete();
    }

    /**
     * Check if a product is in the user's wishlist.
     */
    public function isWishlisted(int $userId, int $productId): bool
    {
        return Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }
}
