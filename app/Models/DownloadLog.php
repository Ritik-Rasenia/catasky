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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
