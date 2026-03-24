<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingFeatureItem extends Model
{
    protected $fillable = ['feature_group_id', 'feature_text', 'sort_order'];
}
