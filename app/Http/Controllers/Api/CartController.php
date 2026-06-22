<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Traits\ApiResponse;
use App\Traits\FormatsProduct;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class CartController extends Controller
{
    use ApiResponse, FormatsProduct;

    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * GET /api/v1/cart
     *
     * List all cart items with summary.
     */
    public function index(): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $summary = $this->cartService->summary($userId);

            $formattedItems = $summary['items']->map(function ($item) {
                return [
                    'id'        => $item->id,
                    'product_id'=> $item->product_id,
                    'quantity'  => $item->quantity,
                    'subtotal'  => (float) $item->subtotal,
                    'product'   => $this->formatProductBasic($item->product),
                    'created_at' => $item->created_at->toISOString(),
                ];
            });

            return $this->success('Cart retrieved successfully.', [
                'items'         => $formattedItems,
                'subtotal'      => $summary['subtotal'],
                'total_items'   => $summary['total_items'],
                'unique_items'  => $summary['unique_items'],
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve cart. Please try again.');
        }
    }

    /**
     * POST /api/v1/cart/add
     *
     * Add a product to the cart.
     */
    public function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $userId = auth('api')->id();
            $cart = $this->cartService->add(
                $userId,
                $request->input('product_id'),
                (int) $request->input('quantity', 1)
            );

            return $this->success('Product added to cart.', [
                'id'        => $cart->id,
                'product_id'=> $cart->product_id,
                'quantity'  => $cart->quantity,
                'subtotal'  => (float) $cart->subtotal,
                'product'   => $this->formatProductBasic($cart->product),
            ], 201);
        } catch (RuntimeException $e) {
            return $this->error(null, $e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('Product not found.');
        } catch (Exception $e) {
            return $this->error(null, 'Failed to add product to cart. Please try again.');
        }
    }

    /**
     * PUT /api/v1/cart/{cart}
     *
     * Update cart item quantity.
     */
    public function update(Request $request, int $cart): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $userId = auth('api')->id();
            $cartItem = $this->cartService->updateQuantity(
                $userId,
                $cart,
                (int) $request->input('quantity')
            );

            return $this->success('Cart updated successfully.', [
                'id' => $cartItem->id,
                'product_id'=> $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'subtotal' => (float) $cartItem->subtotal,
                'product' => $this->formatProductBasic($cartItem->product),
            ]);
        } catch (RuntimeException $e) {
            return $this->error(null, $e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to update cart. Please try again.');
        }
    }

    /**
     * DELETE /api/v1/cart/{cart}
     *
     * Remove an item from the cart.
     */
    public function destroy(int $cart): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $removed = $this->cartService->remove($userId, $cart);

            if (!$removed) {
                return $this->notFound('Cart item not found.');
            }

            return $this->success('Item removed from cart.');
        } catch (Exception $e) {
            return $this->error(null, 'Failed to remove item from cart. Please try again.');
        }
    }

    /**
     * DELETE /api/v1/cart
     *
     * Clear the entire cart.
     */
    public function clear(): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $this->cartService->clear($userId);

            return $this->success('Cart cleared successfully.');
        } catch (Exception $e) {
            return $this->error(null, 'Failed to clear cart. Please try again.');
        }
    }
}
