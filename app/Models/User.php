<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Subscriber Relationships ───────────────────────────────────────────────

    public function subscriberProfile()
    {
        return $this->hasOne(SubscriberProfile::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriberProducts()
    {
        return $this->hasMany(SubscriberProduct::class);
    }

    public function attributeGroups()
    {
        return $this->hasMany(AttributeGroup::class);
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function shareLinks()
    {
        return $this->hasMany(SubscriberShareLink::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // ─── Subscriber Helper Methods ──────────────────────────────────────────────

    public function isSubscriber(): bool
    {
        return $this->hasRole('Subscriber');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function hasActiveSubscription(): bool
    {
        $sub = $this->subscription;
        return $sub && $sub->isActive();
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->latest()
            ->first();
    }
}
