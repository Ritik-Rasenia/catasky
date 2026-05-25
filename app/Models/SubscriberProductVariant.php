<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriberProductVariant extends Model
{
    protected $fillable = [
        'subscriber_product_id', 'variant_sku', 'price', 'stock', 'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }

    public function variantAttributes()
    {
        return $this->hasMany(SubscriberProductVariantAttribute::class, 'variant_id');
    }
}
