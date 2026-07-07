<?php

namespace App\Models\Contest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiReview extends Model
{
    protected $table = 'ai_reviews';

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'provider',
        'model',
        'score',
        'verdict',
        'confidence',
        'raw_response',
        'parsed_result',
        'review_notes',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'reviewed_at',
    ];

    protected $casts = [
        'score'            => 'float',
        'confidence'       => 'float',
        'raw_response'     => 'array',
        'parsed_result'    => 'array',
        'prompt_tokens'    => 'integer',
        'completion_tokens'=> 'integer',
        'total_tokens'     => 'integer',
        'reviewed_at'      => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }
}
