<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDomainLog extends Model
{
    protected $table = 'custom_domain_logs';

    protected $fillable = [
        'custom_domain_id', 'action', 'status', 'message', 'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function customDomain()
    {
        return $this->belongsTo(CustomDomain::class);
    }
}
