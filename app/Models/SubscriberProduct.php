<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriberProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'subcategory_id', 'child_category_id', 'brand_id',
        'name', 'slug', 'sku', 'mrp', 'offer_price', 'price', 'currency',
        'thumbnail', 'short_description', 'full_description', 'tags',
        'stock', 'stock_status', 'meta_title', 'meta_description',
        'featured', 'status', 'approval_status', 'moq',
        'pdf_show_mrp', 'pdf_show_offer_price', 'pdf_show_description',
        'pdf_show_attributes', 'pdf_show_images', 'pdf_show_short_desc',
        'share_show_mrp', 'share_show_offer_price',
        'share_show_description', 'share_show_attributes',
        'sort_order',
    ];

    protected $casts = [
        'brand_id' => 'array',
        'category_id' => 'array',
        'subcategory_id' => 'array',
        'tags' => 'array',
        'mrp' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'featured' => 'boolean',
        'pdf_show_mrp' => 'boolean',
        'pdf_show_offer_price' => 'boolean',
        'pdf_show_description' => 'boolean',
        'pdf_show_attributes' => 'boolean',
        'pdf_show_images' => 'boolean',
        'pdf_show_short_desc' => 'boolean',
        'share_show_mrp' => 'boolean',
        'share_show_offer_price' => 'boolean',
        'share_show_description' => 'boolean',
        'share_show_attributes' => 'boolean',
    ];

    protected $appends = ['thumbnail_url', 'thumbnail_srcset', 'preview_image_url', 'share_image_url', 'discount_percentage', 'description'];

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? $this->optimizedImageUrl($this->thumbnail, 360, 82)
            : asset('uploads/subscriber-products/default.webp');
    }

    public function getPreviewImageUrlAttribute(): string
    {
        return $this->thumbnail
            ? $this->optimizedImageUrl($this->thumbnail, 720, 84)
            : asset('uploads/subscriber-products/default.webp');
    }

    public function getShareImageUrlAttribute(): string
    {
        return $this->thumbnail
            ? $this->optimizedImageUrl($this->thumbnail, 1200, 86)
            : asset('uploads/subscriber-products/default.webp');
    }

    public function getThumbnailSrcsetAttribute(): string
    {
        if (! $this->thumbnail) {
            return '';
        }

        return collect([240, 360, 540, 720])
            ->map(fn (int $width) => $this->optimizedImageUrl($this->thumbnail, $width, 82) . ' ' . $width . 'w')
            ->implode(', ');
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

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->mrp && $this->offer_price && $this->mrp > $this->offer_price) {
            return (int) round((($this->mrp - $this->offer_price) / $this->mrp) * 100);
        }
        return null;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->full_description;
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
            $cachedBrands = \App\Models\Brand::all()->keyBy('id');
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
            $cachedCategories = \App\Models\Category::all()->keyBy('id');
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
            $cachedSubcategories = \App\Models\Subcategory::all()->keyBy('id');
        }
        
        return collect($ids)->map(function($id) use ($cachedSubcategories) {
            return $cachedSubcategories->get($id);
        })->filter();
    }

    public function getSubcategoryAttribute()
    {
        return $this->subcategories->first();
    }

    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class, 'child_category_id');
    }

    public function images()
    {
        return $this->hasMany(SubscriberProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(SubscriberProductImage::class)->where('is_primary', true);
    }

    public function attributeValues()
    {
        return $this->hasMany(SubscriberProductAttributeValue::class);
    }

    public function variants()
    {
        return $this->hasMany(SubscriberProductVariant::class);
    }

    public function shareLinks()
    {
        return $this->hasMany(SubscriberShareLink::class, 'subscriber_product_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(6);
            }
        });
    }
}
