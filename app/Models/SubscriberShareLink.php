<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriberShareLink extends Model
{
    use SoftDeletes;

    protected $table = 'subscriber_share_links';

    protected $fillable = [
        'user_id', 'subscriber_product_id', 'token', 'title', 'type',
        'password', 'settings', 'expires_at', 'view_count',
        'download_count', 'is_active', 'pdf_path', 'approval_status',
    ];

    protected $casts = [
        'settings' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = ['public_url', 'is_expired'];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return route('subscriber.share.public', $this->token);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function incrementView(): void
    {
        $this->increment('view_count');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($link) {
            if (empty($link->token)) {
                $link->token = Str::random(32);
            }
        });
    }
}
