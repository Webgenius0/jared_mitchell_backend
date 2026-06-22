<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ApiResponse;
use App\Traits\FormatsProduct;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse, FormatsProduct;

    /**
     * GET /api/v1/products
     *
     * Returns all active products with their category and gallery images.
     */
    public function index(): JsonResponse
    {
        try {
            $products = Product::with(['category', 'images'])
                ->active()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($product) {
                    return $this->formatProductBasic($product);
                });

            return $this->success(
                'Products retrieved successfully.',
                $products
            );
        } catch (\Exception $e) {
            return $this->error(
                null,
                'Failed to retrieve products. Please try again later.'
            );
        }
    }

    /**
     * GET /api/v1/products/{slug}
     *
     * Returns a single product by its slug.
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $product = Product::with(['category', 'images'])
                ->where('slug', $slug)
                ->first();

            if (!$product || !$product->is_active) {
                return $this->notFound('Product not found.');
            }

            return $this->success(
                'Product retrieved successfully.',
                $this->formatProductDetail($product)
            );
        } catch (\Exception $e) {
            return $this->error(
                null,
                'Failed to retrieve product. Please try again later.'
            );
        }
    }


}
