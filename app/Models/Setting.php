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

    public function getLogoAttribute($value)
    {
        if (empty($value)) return $value;
        return str_starts_with($value, 'uploads/settings/') ? substr($value, 17) : $value;
    }

    public function getFooterLogoAttribute($value)
    {
        if (empty($value)) return $value;
        return str_starts_with($value, 'uploads/settings/') ? substr($value, 17) : $value;
    }

    public function getFaviconAttribute($value)
    {
        if (empty($value)) return $value;
        return str_starts_with($value, 'uploads/settings/') ? substr($value, 17) : $value;
    }

    public function getWatermarkAttribute($value)
    {
        if (empty($value)) return $value;
        return str_starts_with($value, 'uploads/settings/') ? substr($value, 17) : $value;
    }
}
