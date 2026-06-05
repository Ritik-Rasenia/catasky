<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'scope',
        'filename',
        'total_rows',
        'imported_rows',
        'updated_rows',
        'skipped_rows',
        'failed_rows',
        'warning_rows',
        'status',
        'errors',
        'detailed_logs',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'detailed_logs' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
