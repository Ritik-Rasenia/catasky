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
        'child_category_id',
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

    ];

    protected $appends = ['thumbnail_url'];

    public function getThumbnailUrlAttribute()
    {
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

    public function solutions()
    {
        return $this->belongsToMany(Solution::class);
    }

    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class);
    }
}
