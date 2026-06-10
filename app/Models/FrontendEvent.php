<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Frontend-only user interaction events.
 *
 * Tracked exclusively from the public-facing catalogue / store pages.
 * Admin / subscriber panel actions NEVER write to this table.
 *
 * Event taxonomy:
 *   CATALOGUE_SHARE | CATALOGUE_OPEN | PRODUCT_CLICK | PRODUCT_VIEW | PDF_DOWNLOAD | IMAGE_DOWNLOAD
 */
class FrontendEvent extends Model
{
    const UPDATED_AT = null; // immutable event log — no updated_at

    /**
     * Canonical event type constants.
     */
    public const CATALOGUE_SHARE  = 'CATALOGUE_SHARE';
    public const CATALOGUE_OPEN   = 'CATALOGUE_OPEN';
    public const PRODUCT_CLICK    = 'PRODUCT_CLICK';
    public const PRODUCT_VIEW     = 'PRODUCT_VIEW';
    public const PDF_DOWNLOAD     = 'PDF_DOWNLOAD';
    public const IMAGE_DOWNLOAD   = 'IMAGE_DOWNLOAD';

    public const ALL_TYPES = [
        self::CATALOGUE_SHARE,
        self::CATALOGUE_OPEN,
        self::PRODUCT_CLICK,
        self::PRODUCT_VIEW,
        self::PDF_DOWNLOAD,
        self::IMAGE_DOWNLOAD,
    ];

    /**
     * Mapping from legacy / frontend JS event names to canonical types.
     */
    public const LEGACY_MAP = [
        'pdf_download'     => self::PDF_DOWNLOAD,
        'download_pdf'     => self::PDF_DOWNLOAD,
        'image_download'   => self::IMAGE_DOWNLOAD,
        'download_image'   => self::IMAGE_DOWNLOAD,
        'whatsapp_share'   => self::CATALOGUE_SHARE,
        'share_whatsapp'   => self::CATALOGUE_SHARE,
        'other_share'      => self::CATALOGUE_SHARE,
        'share_any'        => self::CATALOGUE_SHARE,
        'link_click'       => self::PRODUCT_CLICK,
        'share_link'       => self::CATALOGUE_SHARE,
        'product_view'     => self::PRODUCT_VIEW,
        'open_product'     => self::PRODUCT_VIEW,
        'catalogue_open'   => self::CATALOGUE_OPEN,
    ];

    protected $fillable = [
        'subscriber_id',
        'catalogue_id',
        'product_id',
        'session_id',
        'event_type',
        'ip_address',
        'user_agent',
        'duration_seconds',
        'meta_json',
    ];

    protected $casts = [
        'meta_json'  => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Accessor for 'event' attribute to maintain compatibility with views/controllers expecting 'event'.
     */
    public function getEventAttribute(): string
    {
        return $this->event_type ?? '';
    }

    /* ------------------------------------------------------------------
     | Relationships
     * ----------------------------------------------------------------- */

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
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
    public function scopeForSubscriber(Builder $query, ?int $subscriberId): Builder
    {
        return $subscriberId ? $query->where('subscriber_id', $subscriberId) : $query;
    }

    /**
     * Scope to a specific catalogue.
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
    public function scopeOfEvent(Builder $query, $type): Builder
    {
        return is_array($type)
            ? $query->whereIn('event_type', $type)
            : $query->where('event_type', $type);
    }

    /**
     * Apply a preset date filter.
     */
    public function scopeDatePreset(Builder $query, string $filter): Builder
    {
        $now = now();

        switch ($filter) {
            case 'today':
                return $query->whereDate('created_at', $now->toDateString());
            case 'yesterday':
                return $query->whereDate('created_at', $now->copy()->subDay()->toDateString());
            case 'last_7_days':
                return $query->whereBetween('created_at', [$now->copy()->subDays(6)->startOfDay(), $now->endOfDay()]);
            case 'last_30_days':
                return $query->whereBetween('created_at', [$now->copy()->subDays(29)->startOfDay(), $now->endOfDay()]);
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

    /**
     * Apply custom date range.
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

    /* ------------------------------------------------------------------
     | Helpers
     * ----------------------------------------------------------------- */

    /**
     * Resolve a legacy/frontend event name to its canonical type.
     */
    public static function resolveEventType(string $raw): string
    {
        $upper = strtoupper(trim($raw));
        if (in_array($upper, self::ALL_TYPES, true)) {
            return $upper;
        }
        $lower = strtolower(trim($raw));
        return self::LEGACY_MAP[$lower] ?? $upper;
    }

    /**
     * Human-readable label for an event type.
     */
    public static function eventLabel(string $event): string
    {
        return match ($event) {
            self::CATALOGUE_SHARE  => 'Catalogue Shared',
            self::CATALOGUE_OPEN   => 'Catalogue Opened',
            self::PRODUCT_CLICK    => 'Product Clicked',
            self::PRODUCT_VIEW     => 'Product Viewed',
            self::PDF_DOWNLOAD     => 'PDF Downloaded',
            self::IMAGE_DOWNLOAD   => 'Image Downloaded',
            default                => ucwords(strtolower(str_replace('_', ' ', $event))),
        };
    }

    /**
     * Bootstrap icon class for an event type.
     */
    public static function eventIcon(string $event): string
    {
        return match ($event) {
            self::CATALOGUE_SHARE  => 'bi-share-fill',
            self::CATALOGUE_OPEN   => 'bi-folder2-open',
            self::PRODUCT_CLICK    => 'bi-cursor-fill',
            self::PRODUCT_VIEW     => 'bi-eye-fill',
            self::PDF_DOWNLOAD     => 'bi-file-earmark-pdf-fill',
            self::IMAGE_DOWNLOAD   => 'bi-image-fill',
            default                => 'bi-lightning-fill',
        };
    }

    /**
     * Bootstrap color for an event type.
     */
    public static function eventColor(string $event): string
    {
        return match ($event) {
            self::CATALOGUE_SHARE  => 'primary',
            self::CATALOGUE_OPEN   => 'info',
            self::PRODUCT_CLICK    => 'success',
            self::PRODUCT_VIEW     => 'warning',
            self::PDF_DOWNLOAD     => 'danger',
            self::IMAGE_DOWNLOAD   => 'secondary',
            default                => 'dark',
        };
    }
}
