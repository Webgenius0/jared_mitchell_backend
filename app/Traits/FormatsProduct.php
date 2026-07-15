<?php

namespace App\Traits;

use App\Models\Product;

trait FormatsProduct
{
    /**
     * Format a product for API list response (minimal public view).
     */
    protected function formatProductBasic(?Product $product): ?array
    {
        if (!$product) {
            return null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'short_description' => $product->short_description,
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
            'display_price' => (float) $product->display_price,
            'discount_percentage' => $product->discount_percentage,
            'type' => $product->type,
            'brand' => $product->brand,
            'is_featured' => $product->is_featured,
            'thumbnail' => $product->thumbnail ? url('/' . $product->thumbnail) : null,
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image' => url('/' . $image->image),
                ];
            }),

            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'stock' => [
                'tracked' => $product->track_stock,
                'quantity' => $product->track_stock ? $product->stock : null,
                'in_stock' => $product->track_stock ? $product->stock > 0 : true,
            ],
            'vendor' => [
                'name' => $product->vendor_name,
                'email' => $product->vendor_email,
                'phone' => $product->vendor_phone,
            ],
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString(),
        ];
    }

    /**
     * Format a product for single-product detail view (includes description & full vendor).
     */
    protected function formatProductDetail(?Product $product): ?array
    {
        if (!$product) {
            return null;
        }

        $data = $this->formatProductBasic($product);

        $data['description'] = $product->description;
        $data['images'] = $product->images->map(function ($image) {
            return [
                'id' => $image->id,
                'image' => url('/' . $image->image),
                'sort_order' => $image->sort_order,
            ];
        });
        $data['vendor']['address'] = $product->vendor_address;
        $data['vendor']['details'] = $product->vendor_details;

        return $data;
    }
}
