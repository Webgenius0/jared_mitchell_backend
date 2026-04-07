<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_id',
        'name',
        'description',
        'image',
        'type',
        'stock',
        'category',
        'price',
        'discount_price',
        'target_audience',
        'delivery_type',
        'sku',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    public function limitedDrops()
    {
        return $this->belongsToMany(LimitedDrop::class)
            ->using(LimitedDropProduct::class)
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->display_id)) {
                $product->display_id = static::generateUnique('display_id', 'P-');
            }

            if (empty($product->sku)) {
                $product->sku = static::generateUnique('sku', 'SKU-');
            }
        });
    }

    /**
     * Generate a unique identifier for the given column.
     */
    protected static function generateUnique(string $column, string $prefix): string
    {
        do {
            $candidate = $prefix . strtoupper(Str::random(10));
        } while (static::where($column, $candidate)->exists());

        return $candidate;
    }
}
