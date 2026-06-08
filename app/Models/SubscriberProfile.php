<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriberProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'company_name', 'company_slug', 'phone', 'website',
        'address', 'city', 'state', 'country', 'pincode', 'gst_number', 'has_gst',
        'logo', 'banner', 'bio', 'whatsapp_number', 'email_for_inquiries',
        'primary_color', 'secondary_color', 'status', 'store_status', 'is_verified',
        'suspended_at', 'suspension_reason', 'custom_domain', 'domain_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'domain_verified' => 'boolean',
        'suspended_at' => 'datetime',
        'has_gst' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customDomain()
    {
        return $this->hasOne(CustomDomain::class, 'user_id', 'user_id');
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }
        return $this->logo
            ? asset('uploads/subscriber-logos/' . $this->logo)
            : asset('uploads/subscriber-logos/default-logo.png');
    }

    public function getBannerUrlAttribute(): string
    {
        if ($this->banner && filter_var($this->banner, FILTER_VALIDATE_URL)) {
            return $this->banner;
        }
        return $this->banner
            ? asset('uploads/subscriber-banners/' . $this->banner)
            : asset('uploads/subscriber-banners/default-banner.png');
    }

    public function isActive(): bool
    {
        return $this->isApproved();
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'active'], true);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
