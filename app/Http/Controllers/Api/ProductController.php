<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;

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
                    return $this->formatProduct($product);
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
                $this->formatProduct($product, true)
            );
        } catch (\Exception $e) {
            return $this->error(
                null,
                'Failed to retrieve product. Please try again later.'
            );
        }
    }

    /**
     * Format a product for API response.
     */
    private function formatProduct(Product $product, bool $includeDescription = false): array
    {
        $data = [
            'id'             => $product->id,
            'name'           => $product->name,
            'slug'           => $product->slug,
            'short_description' => $product->short_description,
            'price'          => (float) $product->price,
            'sale_price'     => $product->sale_price ? (float) $product->sale_price : null,
            'display_price'  => (float) $product->display_price,
            'discount_percentage' => $product->discount_percentage,
            'type'           => $product->type,
            'brand'          => $product->brand,
            'is_featured'    => $product->is_featured,
            'thumbnail'      => $product->thumbnail ? url('/' . $product->thumbnail) : null,
            'category'       => $product->category ? [
                'id'   => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'images'         => $product->images->map(function ($image) {
                return [
                    'id'         => $image->id,
                    'image'      => url('/' . $image->image),
                    'sort_order' => $image->sort_order,
                ];
            }),
            'stock'          => [
                'tracked'     => $product->track_stock,
                'quantity'    => $product->track_stock ? $product->stock : null,
                'in_stock'    => $product->track_stock ? $product->stock > 0 : true,
            ],
            'vendor'         => [
                'name'    => $product->vendor_name,
                'email'   => $product->vendor_email,
                'phone'   => $product->vendor_phone,
            ],
            'created_at'     => $product->created_at->toISOString(),
            'updated_at'     => $product->updated_at->toISOString(),
        ];

        if ($includeDescription) {
            $data['description'] = $product->description;
            $data['vendor']['address'] = $product->vendor_address;
            $data['vendor']['details'] = $product->vendor_details;
        }

        return $data;
    }
}
