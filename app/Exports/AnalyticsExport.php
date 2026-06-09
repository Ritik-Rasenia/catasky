<?php

namespace App\Exports;

use App\Models\VisitLog;
use App\Models\ProductViewLog;
use App\Models\DownloadLog;
use App\Models\OrderLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AnalyticsExport implements WithMultipleSheets
{
    public function __construct(
        protected string $filter = 'all_time',
        protected ?int $userId = null
    ) {}

    public function sheets(): array
    {
        return [
            new AnalyticsVisitSheet($this->filter, $this->userId),
            new AnalyticsProductViewSheet($this->filter, $this->userId),
            new AnalyticsDownloadSheet($this->filter, $this->userId),
            new AnalyticsOrderSheet($this->filter, $this->userId),
        ];
    }
}

class AnalyticsVisitSheet implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected string $filter, protected ?int $userId) {}

    public function collection(): Collection
    {
        $query = VisitLog::with(['shareTrack.user'])->latest('opened_at');

        if ($this->userId) {
            $query->whereHas('shareTrack', fn($q) => $q->where('user_id', $this->userId));
        }

        $this->applyFilter($query, 'opened_at');

        return $query->limit(5000)->get();
    }

    public function headings(): array
    {
        return ['ID', 'Visitor UUID', 'IP Address', 'Device', 'Browser', 'OS', 'Country', 'City', 'Referrer', 'Duration (s)', 'Bounce', 'Opened At'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->visitor_uuid,
            $row->ip_address,
            $row->device_type,
            $row->browser,
            $row->os,
            $row->country,
            $row->city,
            $row->referrer,
            $row->total_time_spent,
            $row->bounce ? 'Yes' : 'No',
            $row->opened_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function applyFilter($query, $column): void
    {
        $now = now();
        switch ($this->filter) {
            case 'today': $query->whereDate($column, $now->toDateString()); break;
            case 'yesterday': $query->whereDate($column, $now->copy()->subDay()->toDateString()); break;
            case 'this_week': $query->whereBetween($column, [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]); break;
            case 'last_30_days': $query->whereBetween($column, [$now->copy()->subDays(30), $now]); break;
            case 'this_month': $query->whereMonth($column, $now->month)->whereYear($column, $now->year); break;
            case 'last_month': $lm = $now->copy()->subMonth(); $query->whereMonth($column, $lm->month)->whereYear($column, $lm->year); break;
            case 'this_year': $query->whereYear($column, $now->year); break;
        }
    }
}

class AnalyticsProductViewSheet implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected string $filter, protected ?int $userId) {}

    public function collection(): Collection
    {
        $query = ProductViewLog::with(['visitLog.shareTrack.user', 'product'])->latest('viewed_at');

        if ($this->userId) {
            $query->whereHas('visitLog.shareTrack', fn($q) => $q->where('user_id', $this->userId));
        }

        $this->applyFilter($query, 'viewed_at');

        return $query->limit(5000)->get();
    }

    public function headings(): array
    {
        return ['ID', 'Product', 'Visitor UUID', 'Duration (s)', 'Browse Order', 'Viewed At'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->product?->name ?? 'Deleted',
            $row->visitLog?->visitor_uuid,
            $row->duration,
            $row->browse_order,
            $row->viewed_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function applyFilter($query, $column): void
    {
        $now = now();
        switch ($this->filter) {
            case 'today': $query->whereDate($column, $now->toDateString()); break;
            case 'yesterday': $query->whereDate($column, $now->copy()->subDay()->toDateString()); break;
            case 'this_week': $query->whereBetween($column, [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]); break;
            case 'last_30_days': $query->whereBetween($column, [$now->copy()->subDays(30), $now]); break;
            case 'this_month': $query->whereMonth($column, $now->month)->whereYear($column, $now->year); break;
            case 'last_month': $lm = $now->copy()->subMonth(); $query->whereMonth($column, $lm->month)->whereYear($column, $lm->year); break;
            case 'this_year': $query->whereYear($column, $now->year); break;
        }
    }
}

class AnalyticsDownloadSheet implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected string $filter, protected ?int $userId) {}

    public function collection(): Collection
    {
        $query = DownloadLog::with(['visitLog', 'user'])->latest('downloaded_at');

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        $this->applyFilter($query, 'downloaded_at');

        return $query->limit(5000)->get();
    }

    public function headings(): array
    {
        return ['ID', 'User', 'IP Address', 'File Type', 'Downloaded At'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->user?->name ?? 'Guest',
            $row->ip_address,
            $row->file_type,
            $row->downloaded_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function applyFilter($query, $column): void
    {
        $now = now();
        switch ($this->filter) {
            case 'today': $query->whereDate($column, $now->toDateString()); break;
            case 'yesterday': $query->whereDate($column, $now->copy()->subDay()->toDateString()); break;
            case 'this_week': $query->whereBetween($column, [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]); break;
            case 'last_30_days': $query->whereBetween($column, [$now->copy()->subDays(30), $now]); break;
            case 'this_month': $query->whereMonth($column, $now->month)->whereYear($column, $now->year); break;
            case 'last_month': $lm = $now->copy()->subMonth(); $query->whereMonth($column, $lm->month)->whereYear($column, $lm->year); break;
            case 'this_year': $query->whereYear($column, $now->year); break;
        }
    }
}

class AnalyticsOrderSheet implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected string $filter, protected ?int $userId) {}

    public function collection(): Collection
    {
        $query = OrderLog::with(['visitLog', 'product'])->latest();

        if ($this->userId) {
            $query->whereHas('shareLink', fn($q) => $q->where('user_id', $this->userId));
        }

        $this->applyFilter($query);

        return $query->limit(5000)->get();
    }

    public function headings(): array
    {
        return ['ID', 'Product', 'Customer', 'Phone', 'Email', 'Qty', 'Total', 'Status', 'Created At'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->product?->name ?? 'N/A',
            $row->customer_name,
            $row->customer_phone,
            $row->customer_email,
            $row->quantity,
            $row->total_price,
            $row->status,
            $row->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function applyFilter($query): void
    {
        $now = now();
        switch ($this->filter) {
            case 'today': $query->whereDate('created_at', $now->toDateString()); break;
            case 'yesterday': $query->whereDate('created_at', $now->copy()->subDay()->toDateString()); break;
            case 'this_week': $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]); break;
            case 'last_30_days': $query->whereBetween('created_at', [$now->copy()->subDays(30), $now]); break;
            case 'this_month': $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year); break;
            case 'last_month': $lm = $now->copy()->subMonth(); $query->whereMonth('created_at', $lm->month)->whereYear('created_at', $lm->year); break;
            case 'this_year': $query->whereYear('created_at', $now->year); break;
        }
    }
}
