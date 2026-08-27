<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'template_style',
        'primary_color',
        'banner_image_url',
        'cta_button_text',
        'cta_button_url',
        'topic_type',
        'ai_prompt',
        'html_content',
        'total_subscribers',
        'sent_count',
        'failed_count',
        'status',
    ];

    protected $casts = [
        'total_subscribers' => 'integer',
        'sent_count'        => 'integer',
        'failed_count'      => 'integer',
    ];
}
