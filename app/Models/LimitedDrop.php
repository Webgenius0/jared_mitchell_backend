<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimitedDrop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'start_time',
        'end_time',
        'price',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->using(LimitedDropProduct::class)
            ->withTimestamps();
    }
}
