<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_log_id',
        'subscriber_share_link_id',
        'subscriber_product_id',
        'quantity',
        'total_price',
        'customer_name',
        'customer_phone',
        'customer_email',
        'message',
        'status',
    ];

    public function visitLog()
    {
        return $this->belongsTo(VisitLog::class, 'visit_log_id');
    }

    public function shareLink()
    {
        return $this->belongsTo(SubscriberShareLink::class, 'subscriber_share_link_id');
    }

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }
}
