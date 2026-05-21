<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareTrackingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'share_id',
        'event_type',
        'event_time',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'event_time' => 'datetime',
    ];

    public function share()
    {
        return $this->belongsTo(CatalogueShare::class, 'share_id');
    }
}
