<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'subscription_id', 'payment_id', 'invoice_number',
        'subtotal', 'tax', 'total', 'currency', 'status',
        'due_date', 'paid_date', 'line_items', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'line_items' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $last = static::whereYear('created_at', $year)->count() + 1;
        return 'INV-' . $year . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
