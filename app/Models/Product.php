<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'part_code',
        'part_number',
        'thumbnail',
        'short_description',
        'variant',
        'price',
        'specifications',
        'tags',
        'packaging',
        'additional_info',
        'featured',
        'is_future',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sku',
        'sale_price',
        'tax',
        'stock',
        'description',
        'featured_image',
    ];

    protected $appends = ['thumbnail_url', 'average_rating', 'reviews_count'];

    public function getThumbnailUrlAttribute()
    {
        if ($this->featured_image) {
            if (filter_var($this->featured_image, FILTER_VALIDATE_URL)) {
                return $this->featured_image;
            }
            return asset('storage/products/' . $this->featured_image);
        }
        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }
        return asset('uploads/products/' . ($this->thumbnail ?: 'default.png'));
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', true);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }
}
