<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscriber_share_link_id',
        'share_track_id',
        'session_id',
        'visitor_uuid',
        'ip_address',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'referrer',
        'opened_at',
        'closed_at',
        'total_time_spent',
        'bounce',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'bounce' => 'boolean',
    ];

    public function shareLink()
    {
        return $this->belongsTo(SubscriberShareLink::class, 'subscriber_share_link_id');
    }

    public function shareTrack()
    {
        return $this->belongsTo(ShareTrack::class, 'share_track_id');
    }

    public function productViews()
    {
        return $this->hasMany(ProductViewLog::class, 'visit_log_id');
    }

    public function downloads()
    {
        return $this->hasMany(DownloadLog::class, 'visit_log_id');
    }

    public function orders()
    {
        return $this->hasMany(OrderLog::class, 'visit_log_id');
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class, 'visit_log_id');
    }
}
