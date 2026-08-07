<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorApplication extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'why_sponsor',
        'sponsor_title',
        'sponsor_image',
    ];
}
