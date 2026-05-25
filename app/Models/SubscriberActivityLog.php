<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriberActivityLog extends Model
{
    protected $table = 'subscriber_activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id',
        'description', 'properties', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, string $description = '', $subject = null, array $properties = []): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
