<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriberProductVariantAttribute extends Model
{
    protected $fillable = [
        'variant_id', 'attribute_id', 'attribute_value'
    ];

    public function variant()
    {
        return $this->belongsTo(SubscriberProductVariant::class, 'variant_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
