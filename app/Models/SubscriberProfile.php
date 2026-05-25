<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriberProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'company_name', 'company_slug', 'phone', 'website',
        'address', 'city', 'state', 'country', 'pincode', 'gst_number',
        'logo', 'bio', 'whatsapp_number', 'email_for_inquiries',
        'primary_color', 'secondary_color', 'status', 'is_verified',
        'suspended_at', 'suspension_reason',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'suspended_at' => 'datetime',
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
