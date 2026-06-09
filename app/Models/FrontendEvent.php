<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrontendEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'product_id',
        'file_type',
        'meta',
        'ip_address',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'product_id');
    }
}
