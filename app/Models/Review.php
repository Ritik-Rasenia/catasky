<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'reviewer_name',
        'reviewer_email',
        'review_content',
        'images',
        'is_verified_buyer',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
        'is_verified_buyer' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the product associated with this review.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who left this review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to only include active reviews.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
