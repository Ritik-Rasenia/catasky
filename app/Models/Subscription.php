<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'subscription_plan_id', 'status',
        'starts_at', 'ends_at', 'trial_ends_at',
        'auto_renew', 'cancelled_at', 'meta',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        if ($this->status === 'active' && $this->ends_at && $this->ends_at->isFuture()) {
            return true;
        }
        if ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return true;
        }
        return false;
    }

    public function isExpired(): bool
    {
        return in_array($this->status, ['expired', 'cancelled']) ||
               ($this->ends_at && $this->ends_at->isPast() && $this->status !== 'trial') ||
               ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast());
    }

    public function daysRemaining(): int
    {
        if ($this->status === 'trial' && $this->trial_ends_at) {
            return max(0, (int) Carbon::now()->diffInDays($this->trial_ends_at, false));
        }
        if ($this->ends_at) {
            return max(0, (int) Carbon::now()->diffInDays($this->ends_at, false));
        }
        return 0;
    }
}
