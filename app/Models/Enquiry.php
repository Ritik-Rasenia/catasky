<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Enquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                $isSubscriber = $user->hasRole('Subscriber') || $user->hasRole('subscriber');
                $isDemo = method_exists($user, 'isDemo') && $user->isDemo();

                if ($isSubscriber || $isDemo) {
                    $userId = $user->id;
                    $builder->where(function ($query) use ($userId) {
                        $query->whereHas('product', function ($q) use ($userId) {
                            $q->withoutGlobalScope('tenant')->where('subscriber_id', $userId);
                        })->orWhereHas('subscriberProduct', function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        })->orWhereHas('brand', function ($q) use ($userId) {
                            $q->withoutGlobalScope('tenant')->where('subscriber_id', $userId);
                        });
                    });
                }
            }
        });
    }

    protected $fillable = [
        'product_id',
        'subscriber_product_id',
        'brand_id',
        'name',
        'email',
        'phone',
        'subject',
        'message'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subscriberProduct()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
