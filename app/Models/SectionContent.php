<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionContent extends Model
{
    protected $fillable = [
        'section_id',
        'field_key',
        'field_type',
        'value',
        'locale',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
