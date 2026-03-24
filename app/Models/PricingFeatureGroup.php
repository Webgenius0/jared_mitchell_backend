<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingFeatureGroup extends Model
{
    protected $fillable = ['price_plan_id', 'title', 'sort_order'];

    public function items() {
        return $this->hasMany(PricingFeatureItem::class, 'feature_group_id')
                    ->orderBy('sort_order');
    }

}
