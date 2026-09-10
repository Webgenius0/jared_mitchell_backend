<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or retrieve Product Categories
        $categories = [
            'Electronics & Audio' => ProductCategory::firstOrCreate(
                ['slug' => 'electronics-audio'],
                ['name' => 'Electronics & Audio', 'is_active' => true]
            ),
            'Apparel & Footwear' => ProductCategory::firstOrCreate(
                ['slug' => 'apparel-footwear'],
                ['name' => 'Apparel & Footwear', 'is_active' => true]
            ),
            'Watches & Accessories' => ProductCategory::firstOrCreate(
                ['slug' => 'watches-accessories'],
                ['name' => 'Watches & Accessories', 'is_active' => true]
            ),
            'Home & Workspace' => ProductCategory::firstOrCreate(
                ['slug' => 'home-workspace'],
                ['name' => 'Home & Workspace', 'is_active' => true]
            ),
            'Fitness & Outdoors' => ProductCategory::firstOrCreate(
                ['slug' => 'fitness-outdoors'],
                ['name' => 'Fitness & Outdoors', 'is_active' => true]
            ),
        ];

        // 2. Define 6 Verified Shopify Products with source image URLs
        $productsData = [
            [
                'name'              => 'Apple AirPods Pro (2nd Generation)',
                'slug'              => 'apple-airpods-pro-2nd-gen',
                'category_id'       => $categories['Electronics & Audio']->id,
                'brand'             => 'Apple',
                'type'              => 'physical',
                'price'             => 249.00,
                'sale_price'        => 199.99,
                'stock'             => 150,
                'track_stock'       => true,
                'is_featured'       => true,
                'is_active'         => true,
                'raw_thumbnail'     => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?q=80&w=800&auto=format&fit=crop',
                'short_description' => 'Active Noise Cancellation, Transparency Mode, and Personalized Spatial Audio with dynamic head tracking.',
                'description'       => '<p>Up to 2x more Active Noise Cancellation than the previous generation. Transparency mode lets you hear the world around you. All-new Adaptive Audio intelligently tailors noise control to your environment. Spatial Audio takes immersion to a remarkably personal level.</p><ul><li>H2 Apple Silicon Chip</li><li>Touch control for volume adjust</li><li>MagSafe Charging Case with Speaker & Lanyard Loop</li><li>Up to 6 hours of listening time on a single charge</li></ul>',
                'vendor_name'       => 'Apple Inc. Official Store',
                'vendor_email'      => 'shopify-support@apple.com',
                'vendor_phone'      => '+1 (800) 692-7753',
                'vendor_address'    => 'One Apple Park Way, Cupertino, CA 95014',
                'vendor_details'    => 'Shopify Verified Premium Merchant - Official Apple Store Distribution Channel.',
                'raw_gallery'       => [
                    'https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1588423771073-b8903fbb85b5?q=80&w=800&auto=format&fit=crop',
                ],
            ],
            [
                'name'              => 'Nike Air Max 270 React Sneakers',
                'slug'              => 'nike-air-max-270-react',
                'category_id'       => $categories['Apparel & Footwear']->id,
                'brand'             => 'Nike',
                'type'              => 'physical',
                'price'             => 160.00,
                'sale_price'        => 129.95,
                'stock'             => 85,
                'track_stock'       => true,
                'is_featured'       => true,
                'is_active'         => true,
                'raw_thumbnail'     => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop',
                'short_description' => 'Bold street style meets ultimate cushioning with Nike\'s largest Max Air unit.',
                'description'       => '<p>The Nike Air Max 270 React draws inspiration from iconic Air Max models, showcasing Nike\'s greatest innovations with its large window and fresh array of colors. Foam midsole feels soft and comfortable while the stretchy inner sleeve creates a personalized fit.</p><ul><li>Nike React technology lightweight foam</li><li>Max Air 270 unit delivers responsive cushioning</li><li>Rubber sole traction & durability</li></ul>',
                'vendor_name'       => 'Nike Official Shopify Store',
                'vendor_email'      => 'orders@nike-shopify.com',
                'vendor_phone'      => '+1 (800) 806-6453',
                'vendor_address'    => 'One Bowerman Drive, Beaverton, OR 97005',
                'vendor_details'    => 'Shopify Verified Brand - Direct from Nike Global Logistics Center.',
                'raw_gallery'       => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=800&auto=format&fit=crop',
                ],
            ],
            [
                'name'              => 'Sony WH-1000XM5 Wireless Headphones',
                'slug'              => 'sony-wh-1000xm5-wireless-headphones',
                'category_id'       => $categories['Electronics & Audio']->id,
                'brand'             => 'Sony',
                'type'              => 'physical',
                'price'             => 399.99,
                'sale_price'        => 348.00,
                'stock'             => 60,
                'track_stock'       => true,
                'is_featured'       => true,
                'is_active'         => true,
                'raw_thumbnail'     => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800&auto=format&fit=crop',
                'short_description' => 'Industry-leading noise cancellation with two processors and 8 microphones for crystal clear listening.',
                'description'       => '<p>The WH-1000XM5 headphones rewrite the rules for distraction-free listening. 2 processors control 8 microphones for unprecedented noise canceling and exceptional call quality. With a newly developed driver, DSEE Extreme and Hi-Res audio support, the WH-1000XM5 provides awe-inspiring audio quality.</p><ul><li>Up to 30-hour battery life with quick charging</li><li>Ultra-comfortable lightweight design with soft fit leather</li><li>Speak-to-chat feature automatically reduces volume during conversations</li></ul>',
                'vendor_name'       => 'Sony Electronics Direct',
                'vendor_email'      => 'store@sony.com',
                'vendor_phone'      => '+1 (855) 860-7669',
                'vendor_address'    => '25 Madison Avenue, New York, NY 10010',
                'vendor_details'    => 'Shopify Verified Enterprise Vendor - Authorized Sony Distributorship.',
                'raw_gallery'       => [
                    'https://images.unsplash.com/photo-1484704849700-f032a568e944?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?q=80&w=800&auto=format&fit=crop',
                ],
            ],
            [
                'name'              => 'Nordgreen Copenhagen Chronograph Watch',
                'slug'              => 'nordgreen-copenhagen-chronograph-watch',
                'category_id'       => $categories['Watches & Accessories']->id,
                'brand'             => 'Nordgreen',
                'type'              => 'physical',
                'price'             => 219.00,
                'sale_price'        => 169.00,
                'stock'             => 120,
                'track_stock'       => true,
                'is_featured'       => true,
                'is_active'         => true,
                'raw_thumbnail'     => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800&auto=format&fit=crop',
                'short_description' => 'Scandi-inspired minimalist design with Japanese Quartz Movement and genuine Italian leather strap.',
                'description'       => '<p>Designed in Copenhagen by Jakob Wagner, Nordgreen watches combine Danish minimalism with sustainable craftsmanship. Features 316L Stainless Steel casing, 3ATM water resistance, and scratch-resistant sapphire crystal glass.</p><ul><li>Interchangeable genuine leather strap</li><li>Japanese Quartz Movement</li><li>Sustainable carbon-neutral delivery</li></ul>',
                'vendor_name'       => 'Nordgreen Copenhagen',
                'vendor_email'      => 'hello@nordgreen.com',
                'vendor_phone'      => '+45 71 99 28 88',
                'vendor_address'    => 'Nørrebrogade 18B, 2200 Copenhagen, Denmark',
                'vendor_details'    => 'Shopify Verified Danish Designer Merchant - Sustainable Luxury Timepieces.',
                'raw_gallery'       => [
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=800&auto=format&fit=crop',
                ],
            ],
            [
                'name'              => 'Herman Miller Aeron Ergonomic Office Chair',
                'slug'              => 'herman-miller-aeron-ergonomic-office-chair',
                'category_id'       => $categories['Home & Workspace']->id,
                'brand'             => 'Herman Miller',
                'type'              => 'physical',
                'price'             => 1295.00,
                'sale_price'        => 1099.00,
                'stock'             => 30,
                'track_stock'       => true,
                'is_featured'       => true,
                'is_active'         => true,
                'raw_thumbnail'     => 'https://images.unsplash.com/photo-1580481072645-022f9a6d8310?q=80&w=800&auto=format&fit=crop',
                'short_description' => 'The pinnacle of ergonomic seating featuring Pellicle 8Z mesh and PostureFit SL back support.',
                'description'       => '<p>Designed by Bill Stumpf and Don Chadwick, the Aeron Chair revolutionized office seating. Incorporating more than 20 years of research on biomechanics and ergonomics, Aeron is engineered to support the human body through natural movement and posture correction.</p><ul><li>8Z Pellicle suspension elastomeric mesh</li><li>Fully adjustable arms and tilt limiter</li><li>12-year official manufacturer warranty</li></ul>',
                'vendor_name'       => 'Herman Miller Direct',
                'vendor_email'      => 'commercial@hermanmiller.com',
                'vendor_phone'      => '+1 (888) 443-4357',
                'vendor_address'    => '855 East Main Ave, Zeeland, MI 49464',
                'vendor_details'    => 'Shopify Verified Luxury Workplace Brand - 12 Year Official Manufacturer Warranty.',
                'raw_gallery'       => [
                    'https://images.unsplash.com/photo-1505797149-43b0069ec26b?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=800&auto=format&fit=crop',
                ],
            ],
            [
                'name'              => 'Hydro Flask 32 oz Wide Mouth Vacuum Water Bottle',
                'slug'              => 'hydro-flask-32-oz-wide-mouth-water-bottle',
                'category_id'       => $categories['Fitness & Outdoors']->id,
                'brand'             => 'Hydro Flask',
                'type'              => 'physical',
                'price'             => 44.95,
                'sale_price'        => 37.99,
                'stock'             => 250,
                'track_stock'       => true,
                'is_featured'       => true,
                'is_active'         => true,
                'raw_thumbnail'     => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?q=80&w=800&auto=format&fit=crop',
                'short_description' => 'TempShield double-wall vacuum insulation keeps beverages cold up to 24 hours or hot up to 12 hours.',
                'description'       => '<p>Big enough for a whole day on the trail or at the gym, the 32 oz Wide Mouth Bottle is made with professional-grade stainless steel and a wider opening for faster refill. Color Last powder coat is dishwasher safe, keeping your bottle slip-free and colorful.</p><ul><li>18/8 Pro-Grade Stainless Steel Construction</li><li>BPA-Free & Phthalate-Free</li><li>Dishwasher safe with leakproof Flex Cap</li></ul>',
                'vendor_name'       => 'Hydro Flask Outdoors',
                'vendor_email'      => 'support@hydroflask.com',
                'vendor_phone'      => '+1 (800) 478-4386',
                'vendor_address'    => '525 NW York Dr, Bend, OR 97703',
                'vendor_details'    => 'Shopify Verified Outdoor Merchant - TempShield Double Wall Vacuum Insulation.',
                'raw_gallery'       => [
                    'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=800&auto=format&fit=crop',
                ],
            ],
        ];

        // 3. Seed Products and Store Local Image Files
        foreach ($productsData as $data) {
            $rawThumbnail = $data['raw_thumbnail'];
            $rawGallery   = $data['raw_gallery'] ?? [];

            unset($data['raw_thumbnail'], $data['raw_gallery']);

            // Download & store local thumbnail path (e.g. storage/uploads/products/thumbnails/1785320924-cg1dzD57.jpg)
            $data['thumbnail'] = $this->downloadAndSaveImage($rawThumbnail, 'thumbnails');

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Download & store local gallery images (e.g. storage/uploads/products/images/1785320924-cg1dzD57.jpg)
            ProductImage::where('product_id', $product->id)->delete();
            foreach ($rawGallery as $index => $galleryUrl) {
                $localImagePath = $this->downloadAndSaveImage($galleryUrl, 'images');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $localImagePath,
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }

    /**
     * Download an image URL and save locally into public storage, returning path formatted like:
     * storage/uploads/products/thumbnails/1785320924-cg1dzD57.jpg
     */
    private function downloadAndSaveImage(string $url, string $subfolder): string
    {
        $fileName = time() . '-' . Str::random(8) . '.jpg';
        $relativeFolder = 'uploads/products/' . trim($subfolder, '/');
        
        $storageDir = storage_path('app/public/' . $relativeFolder);
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $publicDir = public_path('storage/' . $relativeFolder);
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $storagePath = $storageDir . '/' . $fileName;
        $publicPath  = $publicDir . '/' . $fileName;

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'    => 15,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $contents = @file_get_contents($url, false, $context);
            if ($contents !== false && strlen($contents) > 100) {
                file_put_contents($storagePath, $contents);
                file_put_contents($publicPath, $contents);
                return 'storage/' . $relativeFolder . '/' . $fileName;
            }
        } catch (\Throwable $e) {
            // Fallback if network download fails
        }

        // Generate solid color image fallback if download failed
        if (function_exists('imagecreatetruecolor')) {
            $img = imagecreatetruecolor(600, 600);
            $bgColor = imagecolorallocate($img, rand(30, 120), rand(30, 120), rand(30, 120));
            imagefill($img, 0, 0, $bgColor);
            imagejpeg($img, $storagePath, 85);
            imagejpeg($img, $publicPath, 85);
            imagedestroy($img);
        }

        return 'storage/' . $relativeFolder . '/' . $fileName;
    }
}
