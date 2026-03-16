<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
     protected $fillable = [
        'plan_name','badge_text','price','price_suffix',
        'best_for','outcome_text','button_label',
        'button_url','is_featured','is_visible','sort_order'
    ];

    public function featureGroups() {
        return $this->hasMany(PricingFeatureGroup::class, 'price_plan_id')
                    ->orderBy('sort_order');
    }
}
