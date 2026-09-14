<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShopifyService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;

    protected $shopifyService;

    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    /**
     * GET /api/v1/products
     *
     * Returns all active products with their category and gallery images.
     */
    public function index(): JsonResponse
    {
        try {
            $products = $this->shopifyService->getProducts();

            return $this->success(
                'Products retrieved successfully.',
                $products
            );
        } catch (\Exception $e) {
            return $this->error(
                null,
                'Failed to retrieve products: ' . $e->getMessage()
            );
        }
    }

    /**
     * GET /api/v1/products/featured
     *
     * Returns all active featured products with their category and gallery images.
     */
    public function featured(): JsonResponse
    {
        try {
            $products = $this->shopifyService->getFeaturedProducts();

            return $this->success(
                'Featured products retrieved successfully.',
                $products
            );
        } catch (Exception $e) {
            return $this->error(
                null,
                'Failed to retrieve featured products: ' . $e->getMessage()
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
            $product = $this->shopifyService->getProductBySlug($slug);

            if (! $product) {
                return $this->notFound('Product not found.');
            }

            return $this->success(
                'Product retrieved successfully.',
                $product
            );
        } catch (\Exception $e) {
            return $this->error(
                null,
                'Failed to retrieve product: ' . $e->getMessage()
            );
        }
    }
}
