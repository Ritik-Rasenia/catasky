<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriberProductImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subscriber_product_id', 'image_path', 'alt_text', 'is_primary', 'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected $appends = ['image_url', 'preview_url', 'share_url'];

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->optimizedImageUrl($this->image_path, 360, 82);
    }

    public function getPreviewUrlAttribute(): string
    {
        return $this->optimizedImageUrl($this->image_path, 720, 84);
    }

    public function getShareUrlAttribute(): string
    {
        return $this->optimizedImageUrl($this->image_path, 1200, 86);
    }

    public function optimizedImageUrl(?string $path, int $width = 720, int $quality = 82): string
    {
        if (! $path) {
            return asset('uploads/subscriber-products/default.webp');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $separator = str_contains($path, '?') ? '&' : '?';
            return $path . $separator . http_build_query([
                'auto' => 'format',
                'fit' => 'crop',
                'fm' => 'webp',
                'q' => $quality,
                'w' => $width,
            ]);
        }

        return str_starts_with($path, 'uploads/')
            ? asset($path)
            : asset('uploads/subscriber-products/' . $path);
    }
}
