<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use App\Models\ProductImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ProductImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(public int $importLogId) {}

    public function handle(): void
    {
        $log = ProductImportLog::findOrFail($this->importLogId);
        $base = 'imports/'.$this->importLogId;
        $relativeExcel = $base.'/products.xlsx';

        if (! Storage::disk('local')->exists($relativeExcel)) {
            $log->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => array_merge($log->errors ?? [], [
                    ['row' => null, 'message' => 'Import spreadsheet is missing from storage.'],
                ]),
            ]);

            return;
        }

        $absoluteExcel = Storage::disk('local')->path($relativeExcel);
        $imagesPath = Storage::disk('local')->path($base.'/images');
        $workPath = Storage::disk('local')->path($base);

        $this->hydrateTotalRows($log, $absoluteExcel);

        $log->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            Excel::import(new ProductsImport($this->importLogId, $imagesPath), $relativeExcel, 'local');
            $log->refresh();
            if ($log->status !== 'failed') {
                $log->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        } catch (Throwable $e) {
            $log->refresh();
            $log->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => array_merge($log->errors ?? [], [
                    ['row' => null, 'message' => $e->getMessage()],
                ]),
            ]);
        } finally {
            if (is_dir($workPath)) {
                File::deleteDirectory($workPath);
            }
        }
    }

    private function hydrateTotalRows(ProductImportLog $log, string $absoluteExcel): void
    {
        try {
            $reader = IOFactory::createReaderForFile($absoluteExcel);
            $info = $reader->listWorksheetInfo($absoluteExcel);
            $sheetRows = (int) ($info[0]['totalRows'] ?? 1);
            $dataRows = max(0, $sheetRows - 1);
            $log->update(['total_rows' => $dataRows]);
        } catch (Throwable) {
            // total_rows stays 0; UI shows indeterminate progress
        }
    }
}
