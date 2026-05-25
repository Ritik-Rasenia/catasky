<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'currency',
        'duration_days', 'product_limit', 'attribute_limit',
        'share_link_limit', 'pdf_sharing', 'image_sharing',
        'watermark_removal', 'custom_branding', 'analytics',
        'features', 'is_trial', 'trial_days', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'pdf_sharing' => 'boolean',
        'image_sharing' => 'boolean',
        'watermark_removal' => 'boolean',
        'custom_branding' => 'boolean',
        'analytics' => 'boolean',
        'is_trial' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price == 0) {
            return 'Free';
        }
        return '₹' . number_format($this->price, 2);
    }
}
