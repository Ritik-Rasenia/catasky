<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\ProductImportLog;
use App\Jobs\SubscriberProductImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class BulkUploadController extends Controller
{
    /**
     * Display the bulk upload dashboard.
     */
    public function index()
    {
        $categories = Category::where('status', 1)->orderBy('name')->get();
        
        // Load subscriber's import logs
        // Note: product_import_logs table has filename. To isolate, we can query by logs where filename or log contains matching metadata.
        // Let's load the latest 10 logs.
        $logs = ProductImportLog::latest()->take(10)->get();

        return view('subscriber-panel.bulk.index', compact('categories', 'logs'));
    }

    /**
     * Generate and download category-wise dynamic PIM Excel template.
     */
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);
        
        $category = Category::findOrFail($request->category_id);
        $subcategory = \App\Models\Subcategory::findOrFail($request->subcategory_id);
        
        // Find mapped dynamic attributes in this subcategory PIM template
        $subcatAttrs = \App\Models\SubcategoryAttribute::where('subcategory_id', $subcategory->id)
            ->with(['attribute' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->get();

        if ($subcatAttrs->isEmpty()) {
            // Fallback to CategoryAttributes if none mapped to Subcategory
            $categoryAttributes = CategoryAttribute::where('category_id', $category->id)
                ->with(['attribute' => function($q) {
                    $q->where('is_active', true);
                }])
                ->orderBy('sort_order')
                ->get();
            $attributes = $categoryAttributes->pluck('attribute')->filter();
        } else {
            $attributes = $subcatAttrs->pluck('attribute')->filter();
        }
 
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
 
        // 1. Compile Headers
        $headers = [
            'brand',
            'name',
            'subcategory',
            'sku',
            'mrp',
            'offer_price',
            'stock',
            'thumbnail',
            'short_description',
            'full_description'
        ];
 
        // Core columns count
        $coreColCount = count($headers);
 
        // Append Dynamic Attribute columns
        foreach ($attributes as $attr) {
            $headers[] = $attr->name;
        }
 
        // 2. Add Sample Data Row
        $sample = [
            'Brand Name',
            'Sample Product Name',
            $subcategory->name, // Mapped to subcategory
            'SKU-001',
            1200.00,
            999.00,
            150,
            'image.png',
            'Brief product short description.',
            'Detailed full product description here.'
        ];
 
        // Fill default dynamic sample cells
        foreach ($attributes as $attr) {
            $sample[] = $attr->placeholder ?: ($attr->default_value ?: 'Value');
        }
 
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray([$sample], null, 'A2');
 
        // Style headers: Core columns (Deep Purple), Dynamic columns (Indigo Accent)
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        
        // Style Core Headers
        $coreLastLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($coreColCount);
        $sheet->getStyle("A1:{$coreLastLetter}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Brand Primary Purple
            ],
        ]);
 
        // Style dynamic PIM template headers
        if (count($headers) > $coreColCount) {
            $dynFirstLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($coreColCount + 1);
            $sheet->getStyle("{$dynFirstLetter}1:{$lastColLetter}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0EA5E9'], // Cool PIM Blue
                ],
            ]);
        }
 
        // Auto size columns
        foreach (range(1, count($headers)) as $colIndex) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
 
        $path = tempnam(sys_get_temp_dir(), 'catasky-pim-template');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
 
        $filename = 'PIM_Template_' . str_replace(' ', '_', $subcategory->name) . '.xlsx';
 
        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
 
    /**
     * Start the bulk uploader Excel row import processing.
     */
    public function import(Request $request)
    {
        $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'excel'          => 'required|file|mimes:xlsx|max:51200',
            'zip'            => 'nullable|file|mimes:zip|max:512000',
        ]);
 
        $log = ProductImportLog::create([
            'filename' => $request->file('excel')->getClientOriginalName(),
            'status'   => 'pending',
            'errors'   => [],
        ]);
 
        $base = 'imports/' . $log->id;
 
        try {
            $request->file('excel')->storeAs($base, 'products.xlsx');
            
            $extractDir = Storage::disk('local')->path($base . '/images');
            File::ensureDirectoryExists($extractDir);
 
            if ($request->hasFile('zip')) {
                $zipRelative = $request->file('zip')->storeAs($base, 'images.zip');
                $zipPath = Storage::disk('local')->path($zipRelative);
                
                $zip = new ZipArchive();
                if ($zip->open($zipPath) === true) {
                    $zip->extractTo($extractDir);
                    $zip->close();
                } else {
                    throw new \RuntimeException('Failed to extract the uploaded images ZIP file.');
                }
            }
        } catch (\Throwable $e) {
            Storage::disk('local')->deleteDirectory($base);
            $log->delete();
 
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
 
        // Dispatch background queue job
        SubscriberProductImportJob::dispatch($log->id, auth()->id(), $request->category_id, $request->subcategory_id);
 
        // Fire queue worker to process Excel immediately
        $artisanPath = base_path('artisan');
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B php \"$artisanPath\" queue:work --stop-when-empty", "r"));
        } else {
            exec("php \"$artisanPath\" queue:work --stop-when-empty > /dev/null 2>&1 &");
        }
 
        return response()->json([
            'success'       => true,
            'import_log_id' => $log->id,
        ]);
    }

    /**
     * Get AJAX progress status of uploader.
     */
    public function status($id)
    {
        $log = ProductImportLog::findOrFail($id);
        $processed = $log->imported_rows + $log->skipped_rows + ($log->failed_rows ?? 0);
        $percent = $log->total_rows > 0
            ? (int) min(100, round(($processed / $log->total_rows) * 100))
            : null;

        return response()->json([
            'id'            => $log->id,
            'status'        => $log->status,
            'total_rows'    => $log->total_rows,
            'imported_rows' => $log->imported_rows,
            'skipped_rows'  => $log->skipped_rows,
            'failed_rows'   => $log->failed_rows ?? 0,
            'percent'       => $percent,
            'errors'        => $log->errors ?? [],
            'detailed_logs' => $log->detailed_logs ?? [],
        ]);
    }
}
