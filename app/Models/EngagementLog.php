<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngagementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_log_id',
        'subscriber_share_link_id',
        'user_id',
        'event_type',
        'subscriber_product_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function visitLog()
    {
        return $this->belongsTo(VisitLog::class, 'visit_log_id');
    }

    public function shareLink()
    {
        return $this->belongsTo(SubscriberShareLink::class, 'subscriber_share_link_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id')->withTrashed();
    }
}
