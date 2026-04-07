<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LimitedDropProduct extends Pivot
{
    protected $table = 'limited_drop_product';

    protected $fillable = [
        'limited_drop_id',
        'product_id',
    ];
}
