<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductViewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_log_id',
        'subscriber_product_id',
        'viewed_at',
        'duration',
        'browse_order',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function visitLog()
    {
        return $this->belongsTo(VisitLog::class, 'visit_log_id');
    }

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }
}
