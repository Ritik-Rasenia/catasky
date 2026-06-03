<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;

class Product extends Model
{
    use HasFactory, SoftDeletes, Multitenantable;

    protected $fillable = [
        'brand_id',
        'subscriber_id',
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'part_code',
        'part_number',
        'sku',
        'thumbnail',
        'short_description',
        'variant',
        'price',
        'mrp',
        'offer_price',
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
        'sale_price',
        'tax',
        'stock',
        'description',
        'featured_image',
        'moq',
    ];

    protected $casts = [
        'brand_id' => 'array',
        'category_id' => 'array',
        'subcategory_id' => 'array',
    ];

    protected $appends = ['thumbnail_url', 'average_rating', 'reviews_count'];

    public function getThumbnailUrlAttribute()
    {
        if ($this->featured_image) {
            if (filter_var($this->featured_image, FILTER_VALIDATE_URL)) {
                return $this->featured_image;
            }
            return str_starts_with($this->featured_image, 'storage/products/')
                ? asset($this->featured_image)
                : asset('storage/products/' . $this->featured_image);
        }
        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }
        $thumb = $this->thumbnail ?: 'default.png';
        return str_starts_with($thumb, 'uploads/products/')
            ? asset($thumb)
            : asset('uploads/products/' . $thumb);
    }

    public function getBrandsAttribute()
    {
        $ids = $this->brand_id;
        if (empty($ids)) {
            return collect();
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        static $cachedBrands = null;
        if ($cachedBrands === null) {
            $cachedBrands = \App\Models\Brand::withoutGlobalScope('tenant')->get()->keyBy('id');
        }
        
        return collect($ids)->map(function($id) use ($cachedBrands) {
            return $cachedBrands->get($id);
        })->filter();
    }

    public function getBrandAttribute()
    {
        return $this->brands->first();
    }

    public function getCategoriesAttribute()
    {
        $ids = $this->category_id;
        if (empty($ids)) {
            return collect();
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        static $cachedCategories = null;
        if ($cachedCategories === null) {
            $cachedCategories = \App\Models\Category::withoutGlobalScope('tenant')->get()->keyBy('id');
        }
        
        return collect($ids)->map(function($id) use ($cachedCategories) {
            return $cachedCategories->get($id);
        })->filter();
    }

    public function getCategoryAttribute()
    {
        return $this->categories->first();
    }

    public function getSubcategoriesAttribute()
    {
        $ids = $this->subcategory_id;
        if (empty($ids)) {
            return collect();
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        static $cachedSubcategories = null;
        if ($cachedSubcategories === null) {
            $cachedSubcategories = \App\Models\Subcategory::withoutGlobalScope('tenant')->get()->keyBy('id');
        }
        
        return collect($ids)->map(function($id) use ($cachedSubcategories) {
            return $cachedSubcategories->get($id);
        })->filter();
    }

    public function getSubcategoryAttribute()
    {
        return $this->subcategories->first();
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
