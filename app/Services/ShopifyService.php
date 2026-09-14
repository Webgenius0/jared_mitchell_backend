<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected $domain;
    protected $token;
    protected $apiVersion = '2024-01';

    public function __construct()
    {
        $this->domain = env('SHOPIFY_STORE_DOMAIN');
        // Admin API uses a different token (starts with shpat_)
        $this->token = env('SHOPIFY_ADMIN_TOKEN', env('SHOPIFY_STOREFRONT_TOKEN'));
    }

    /**
     * Send a GET request to the Admin REST API.
     */
    protected function get(string $endpoint, array $query = [])
    {
        if (!$this->domain || !$this->token) {
            throw new Exception("Shopify Admin credentials are not configured.");
        }

        $url = "https://{$this->domain}/admin/api/{$this->apiVersion}/{$endpoint}";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->get($url, $query);

        if ($response->failed()) {
            Log::error("Shopify Admin API Error: " . $response->body());
            throw new Exception("Failed to communicate with Shopify Admin API. Check your token and permissions.");
        }

        return $response->json();
    }

    /**
     * Send a POST request to the Admin REST API.
     */
    protected function post(string $endpoint, array $data = [])
    {
        if (!$this->domain || !$this->token) {
            throw new Exception("Shopify Admin credentials are not configured.");
        }

        $url = "https://{$this->domain}/admin/api/{$this->apiVersion}/{$endpoint}";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        if ($response->failed()) {
            Log::error("Shopify Admin API Error: " . $response->body());
            throw new Exception("Failed to communicate with Shopify Admin API: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Generate Shopify Cart Permalink URL for direct checkout.
     * $items = [ ['variant_id' => 12345, 'quantity' => 2], ... ]
     */
    public function createCheckoutPermalink(array $items): string
    {
        $parts = [];
        foreach ($items as $item) {
            $variantId = $item['variant_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            if ($variantId) {
                $parts[] = "{$variantId}:{$quantity}";
            }
        }

        $cartString = implode(',', $parts);
        return "https://{$this->domain}/cart/{$cartString}";
    }

    /**
     * Create a Draft Order in Shopify Admin and return the invoice/checkout URL.
     */
    public function createDraftOrder(array $items, array $shippingAddress = [], ?string $email = null): array
    {
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'variant_id' => $item['variant_id'],
                'quantity'   => $item['quantity'],
            ];
        }

        $payload = [
            'draft_order' => [
                'line_items' => $lineItems,
            ]
        ];

        if ($email) {
            $payload['draft_order']['email'] = $email;
        }

        if (!empty($shippingAddress)) {
            $payload['draft_order']['shipping_address'] = [
                'first_name' => $shippingAddress['first_name'] ?? ($shippingAddress['name'] ?? ''),
                'last_name'  => $shippingAddress['last_name'] ?? '',
                'address1'   => $shippingAddress['address_line1'] ?? '',
                'address2'   => $shippingAddress['address_line2'] ?? '',
                'city'       => $shippingAddress['city'] ?? '',
                'province'   => $shippingAddress['state'] ?? '',
                'country'    => $shippingAddress['country'] ?? '',
                'zip'        => $shippingAddress['zip'] ?? '',
                'phone'      => $shippingAddress['phone'] ?? '',
            ];
        }

        $data = $this->post('draft_orders.json', $payload);

        return [
            'id'          => $data['draft_order']['id'] ?? null,
            'invoice_url' => $data['draft_order']['invoice_url'] ?? null,
            'order_id'    => $data['draft_order']['order_id'] ?? null,
        ];
    }

    /**
     * Get all active products.
     */
    public function getProducts(int $limit = 50): array
    {
        $data = $this->get('products.json', [
            'limit' => $limit,
            'status' => 'active'
        ]);

        return $this->formatProducts($data['products'] ?? []);
    }

    /**
     * Get featured products (e.g., tagged with "featured").
     */
    public function getFeaturedProducts(int $limit = 10): array
    {
        // Admin API allows filtering by tags directly
        $data = $this->get('products.json', [
            'limit' => $limit,
            'status' => 'active',
            'tags' => 'featured'
        ]);

        return $this->formatProducts($data['products'] ?? []);
    }

    /**
     * Get a single product by its handle (slug).
     */
    public function getProductBySlug(string $slug): ?array
    {
        $data = $this->get('products.json', [
            'handle' => $slug,
            'limit' => 1
        ]);

        if (empty($data['products'])) {
            return null;
        }

        return $this->formatSingleProduct($data['products'][0], true);
    }

    /**
     * Format a list of products.
     */
    protected function formatProducts(array $shopifyProducts): array
    {
        $products = [];
        foreach ($shopifyProducts as $product) {
            $products[] = $this->formatSingleProduct($product, false);
        }
        return $products;
    }

    /**
     * Format a single Shopify Admin REST product to match the local frontend expectations.
     */
    protected function formatSingleProduct(array $shopifyProduct, bool $isDetail = false): array
    {
        $variant = $shopifyProduct['variants'][0] ?? null;
        
        $price = $variant ? (float) $variant['price'] : 0.0;
        $compareAtPrice = ($variant && isset($variant['compare_at_price'])) ? (float) $variant['compare_at_price'] : null;
        
        // In local logic, display_price is sale_price ?: price.
        $salePrice = null;
        $discountPercentage = 0;
        $displayPrice = $price;

        if ($compareAtPrice && $compareAtPrice > $price) {
            $salePrice = $price;
            $price = $compareAtPrice; // Original price
            $discountPercentage = (int) round((($price - $salePrice) / $price) * 100);
            $displayPrice = $salePrice;
        }

        $images = [];
        if (isset($shopifyProduct['images'])) {
            foreach ($shopifyProduct['images'] as $img) {
                $images[] = [
                    'id' => $img['id'],
                    'image' => $img['src']
                ];
            }
        }

        $isFeatured = str_contains(strtolower($shopifyProduct['tags'] ?? ''), 'featured');

        $variants = [];
        if (isset($shopifyProduct['variants'])) {
            foreach ($shopifyProduct['variants'] as $v) {
                $variants[] = [
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'price' => (float) $v['price'],
                    'sku' => $v['sku'] ?? null,
                    'inventory_quantity' => $v['inventory_quantity'] ?? 0,
                ];
            }
        }

        $formatted = [
            'id' => $shopifyProduct['id'],
            // 'variant_id' => $variant ? $variant['id'] : null,
            
            'name' => $shopifyProduct['title'],
            'slug' => $shopifyProduct['handle'],
            'short_description' => substr(strip_tags($shopifyProduct['body_html'] ?? ''), 0, 150),
            'price' => $price,
            'sale_price' => $salePrice,
            'display_price' => $displayPrice,
            'discount_percentage' => $discountPercentage,
            'type' => $shopifyProduct['product_type'],
            'brand' => $shopifyProduct['vendor'],
            'is_featured' => $isFeatured,
            'thumbnail' => $shopifyProduct['image']['src'] ?? null,
            'images' => $images,
            'variants' => $variants,
            
            'category' => [
                'id' => $shopifyProduct['product_type'],
                'name' => $shopifyProduct['product_type'] ?: 'Uncategorized',
            ],
            'stock' => [
                'tracked' => true,
                'quantity' => $variant ? ($variant['inventory_quantity'] ?? 0) : null,
                'in_stock' => $variant ? (($variant['inventory_quantity'] ?? 0) > 0 || $variant['inventory_policy'] === 'continue') : false,
            ],
            'vendor' => [
                'name' => $shopifyProduct['vendor'],
                'email' => null,
                'phone' => null,
            ],
            'created_at' => $shopifyProduct['created_at'],
            'updated_at' => $shopifyProduct['updated_at'],
        ];

        if ($isDetail) {
            $formatted['description'] = $shopifyProduct['body_html'] ?? '';
            $formatted['vendor']['address'] = null;
            $formatted['vendor']['details'] = null;
        }

        return $formatted;
    }
}
