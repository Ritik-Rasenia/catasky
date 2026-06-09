<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Unified analytics event model.
 *
 * Strict event_type taxonomy:
 *   catalogue_open | product_view | share_whatsapp | share_any
 *   download_pdf   | download_image | enquiry_submit | order_create
 *   session_start  | session_end    | link_click
 */
class AnalyticsEvent extends Model
{
    const UPDATED_AT = null; // immutable event log — no updated_at

    protected $fillable = [
        'user_id',
        'session_id',
        'event_type',
        'product_id',
        'catalogue_id',
        'device',
        'url',
        'referrer',
        'ip_address',
        'user_agent',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    /* ------------------------------------------------------------------
     | Relationships
     * ----------------------------------------------------------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'product_id');
    }

    public function catalogue()
    {
        return $this->belongsTo(SubscriberShareLink::class, 'catalogue_id');
    }

    /* ------------------------------------------------------------------
     | Scopes
     * ----------------------------------------------------------------- */

    /**
     * Scope to a specific subscriber (multi-tenant isolation).
     */
    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    /**
     * Scope to a specific catalogue / share link.
     */
    public function scopeForCatalogue(Builder $query, ?int $catalogueId): Builder
    {
        return $catalogueId ? $query->where('catalogue_id', $catalogueId) : $query;
    }

    /**
     * Scope to one or more event types.
     *
     * @param string|array $type
     */
    public function scopeOfType(Builder $query, $type): Builder
    {
        return is_array($type)
            ? $query->whereIn('event_type', $type)
            : $query->where('event_type', $type);
    }

    /**
     * Apply a date-range filter on created_at.
     */
    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
        }
        return $query;
    }

    /**
     * Apply a preset filter (today, yesterday, this_week, last_30_days, etc.)
     */
    public function scopePresetFilter(Builder $query, string $filter): Builder
    {
        $now = now();

        switch ($filter) {
            case 'today':
                return $query->whereDate('created_at', $now->toDateString());
            case 'yesterday':
                return $query->whereDate('created_at', $now->copy()->subDay()->toDateString());
            case 'this_week':
                return $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            case 'last_30_days':
                return $query->whereBetween('created_at', [$now->copy()->subDays(30), $now]);
            case 'this_month':
                return $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            case 'last_month':
                $lm = $now->copy()->subMonth();
                return $query->whereMonth('created_at', $lm->month)->whereYear('created_at', $lm->year);
            case 'this_year':
                return $query->whereYear('created_at', $now->year);
            default:
                return $query; // all_time
        }
    }
}
