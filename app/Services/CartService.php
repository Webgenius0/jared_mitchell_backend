<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Support\Collection;

class CartService
{
    public function __construct(
        protected ShopifyService $shopifyService
    ) {}

    /**
     * Add a product/variant to the cart. If it already exists, increment quantity.
     *
     * @return Cart
     */
    public function add(int $userId, int|string $productId, int $quantity = 1, int|string|null $variantId = null): Cart
    {
        $query = Cart::where('user_id', $userId)->where('product_id', $productId);
        if ($variantId) {
            $query->where('variant_id', $variantId);
        }

        $existing = $query->first();

        if ($existing) {
            $newQuantity = $existing->quantity + $quantity;
            $existing->update(['quantity' => $newQuantity]);
            return $existing->fresh();
        }

        return Cart::create([
            'user_id'    => $userId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity'   => $quantity,
        ]);
    }

    /**
     * Update the quantity of a cart item.
     *
     * @return Cart
     */
    public function updateQuantity(int $userId, int $cartId, int $quantity): Cart
    {
        $cart = Cart::where('user_id', $userId)->findOrFail($cartId);

        if ($quantity < 1) {
            throw new \RuntimeException('Quantity must be at least 1.');
        }

        $cart->update(['quantity' => $quantity]);

        return $cart->fresh();
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(int $userId, int $cartId): bool
    {
        return Cart::where('user_id', $userId)
            ->where('id', $cartId)
            ->delete() > 0;
    }

    /**
     * List all items in the user's cart with Shopify product details.
     */
    public function list(int $userId): Collection
    {
        $cartItems = Cart::where('user_id', $userId)
            ->latest()
            ->get();

        if ($cartItems->isEmpty()) {
            return collect();
        }

        try {
            $shopifyProducts = collect($this->shopifyService->getProducts(100));
        } catch (\Exception $e) {
            $shopifyProducts = collect();
        }

        return $cartItems->map(function (Cart $item) use ($shopifyProducts) {
            // Match Shopify product by product_id
            $product = $shopifyProducts->firstWhere('id', (int) $item->product_id)
                ?: $shopifyProducts->firstWhere('variant_id', (int) ($item->variant_id ?: $item->product_id));

            $price = $product['display_price'] ?? ($product['price'] ?? 0);
            $subtotal = round($price * $item->quantity, 2);

            $item->product = $product ?: [
                'id' => $item->product_id,
                'name' => 'Shopify Product',
                'price' => 0,
            ];
            $item->subtotal = $subtotal;

            return $item;
        });
    }

    /**
     * Clear the entire cart.
     */
    public function clear(int $userId): void
    {
        Cart::where('user_id', $userId)->delete();
    }

    /**
     * Get cart summary with totals.
     */
    public function summary(int $userId): array
    {
        $items = $this->list($userId);

        $subtotal = $items->sum(function ($item) {
            return $item->subtotal ?? 0;
        });

        $totalItems = $items->sum('quantity');
        $uniqueItems = $items->count();

        return [
            'items'        => $items,
            'subtotal'     => round($subtotal, 2),
            'total_items'  => $totalItems,
            'unique_items' => $uniqueItems,
        ];
    }
}
