<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_title',
        'site_description',
        'logo',
        'footer_logo',
        'favicon',
        'email',
        'phone',
        'address',
        'admin_email',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'primary_color',
        'secondary_color',
        'font_family',
        'watermark',
        'pdf_cover_style',
        'meta_keywords',
    ];
}
