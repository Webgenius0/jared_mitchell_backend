<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class BusinessMedia extends Model
{
    protected $table = 'business_media';

    protected $fillable = [
        'business_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    /**
     * The business this media belongs to.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
