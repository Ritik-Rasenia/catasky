<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriberPdfTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'subscriber_pdf_templates';

    protected $fillable = [
        'user_id', 'name', 'show_logo', 'show_watermark', 'watermark_text',
        'show_qr_code', 'show_page_numbers', 'brand_color', 'accent_color',
        'layout', 'paper_size', 'orientation', 'header_text', 'footer_text',
        'is_default',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_watermark' => 'boolean',
        'show_qr_code' => 'boolean',
        'show_page_numbers' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
