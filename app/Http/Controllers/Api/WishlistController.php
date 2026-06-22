<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WishlistService;
use App\Traits\ApiResponse;
use App\Traits\FormatsProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    use ApiResponse, FormatsProduct;

    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * GET /api/v1/wishlist
     *
     * List all wishlist items for the authenticated user.
     */
    public function index(): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $items = $this->wishlistService->list($userId);

            $formatted = $items->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'product_id' => $item->product_id,
                    'product'    => $this->formatProductBasic($item->product),
                    'created_at' => $item->created_at->toISOString(),
                ];
            });

            return $this->success('Wishlist retrieved successfully.', $formatted);
        } catch (\Exception $e) {
            return $this->error(null, 'Failed to retrieve wishlist. Please try again.');
        }
    }

    /**
     * POST /api/v1/wishlist/toggle/{product}
     *
     * Toggle (add/remove) a product from the wishlist.
     */
    public function toggle(int $product): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $result = $this->wishlistService->toggle($userId, $product);

            $message = $result['is_wishlisted']
                ? 'Product added to wishlist.'
                : 'Product removed from wishlist.';

            return $this->success($message, $result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Product not found.');
        } catch (\Exception $e) {
            return $this->error(null, 'Failed to update wishlist. Please try again.');
        }
    }

    /**
     * DELETE /api/v1/wishlist/{product}
     *
     * Remove a specific product from the wishlist.
     */
    public function destroy(int $product): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $removed = $this->wishlistService->remove($userId, $product);

            if (!$removed) {
                return $this->notFound('Product not found in wishlist.');
            }

            return $this->success('Product removed from wishlist.');
        } catch (\Exception $e) {
            return $this->error(null, 'Failed to remove from wishlist. Please try again.');
        }
    }

    /**
     * DELETE /api/v1/wishlist
     *
     * Clear the entire wishlist.
     */
    public function clear(): JsonResponse
    {
        try {
            $userId = auth('api')->id();
            $this->wishlistService->clear($userId);

            return $this->success('Wishlist cleared successfully.');
        } catch (\Exception $e) {
            return $this->error(null, 'Failed to clear wishlist. Please try again.');
        }
    }

}
