<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscriber_share_link_id',
        'subscriber_product_id',
        'tracking_token',
        'channel',
        'shared_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shareLink()
    {
        return $this->belongsTo(SubscriberShareLink::class, 'subscriber_share_link_id');
    }

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }

    public function visitLogs()
    {
        return $this->hasMany(VisitLog::class, 'share_track_id');
    }
}
