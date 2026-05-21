<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogueShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'catalogue_code',
        'product_ids',
        'pdf_url',
        'customer_phone',
        'message_id',
        'delivery_status',
        'seen_status',
        'clicked_status',
        'opened_status',
        'total_view_time',
        'visit_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trackingLogs()
    {
        return $this->hasMany(ShareTrackingLog::class, 'share_id');
    }
}
