<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_log_id',
        'subscriber_share_link_id',
        'user_id',
        'ip_address',
        'file_type',
        'downloaded_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function visitLog()
    {
        return $this->belongsTo(VisitLog::class, 'visit_log_id');
    }

    public function shareLink()
    {
        return $this->belongsTo(SubscriberShareLink::class, 'subscriber_share_link_id');
    }

    public function shareTrack()
    {
        return $this->hasOneThrough(
            ShareTrack::class,
            SubscriberShareLink::class,
            'id',                          // Foreign key on subscriber_share_links
            'subscriber_share_link_id',    // Foreign key on share_tracks
            'subscriber_share_link_id',    // Local key on download_logs
            'id'                           // Local key on subscriber_share_links
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
