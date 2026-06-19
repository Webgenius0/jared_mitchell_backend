<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',

        'price',
        'sale_price',

        'stock',
        'track_stock',

        'type',
        'brand',

        'thumbnail',

        'vendor_name',
        'vendor_email',
        'vendor_phone',
        'vendor_address',
        'vendor_details',

        'category_id',

        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'sale_price'    => 'decimal:2',
        'track_stock'   => 'boolean',
        'is_featured'   => 'boolean',
        'is_active'     => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function categories()
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'product_category'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getDisplayPriceAttribute(): float
    {
        return $this->sale_price ?: $this->price;
    }

    public function getDiscountAmountAttribute(): float
    {
        if (!$this->sale_price) {
            return 0;
        }

        return $this->price - $this->sale_price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->sale_price || $this->price <= 0) {
            return 0;
        }

        return (int) round(
            (($this->price - $this->sale_price) / $this->price) * 100
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePhysical($query)
    {
        return $query->where('type', 'physical');
    }

    public function scopeDigital($query)
    {
        return $query->where('type', 'digital');
    }

    public function scopeService($query)
    {
        return $query->where('type', 'service');
    }
}
