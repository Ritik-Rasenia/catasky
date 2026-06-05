<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

$filename = 'test_export_out.xlsx';
$filePath = storage_path('app/' . $filename);
if (file_exists($filePath)) {
    unlink($filePath);
}

// Perform export
Excel::store(new ProductsExport(), $filename, 'local');

if (file_exists($filePath)) {
    echo "Exported file created successfully at storage/app/$filename.\n";
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);
    
    // Print headers
    if (!empty($rows)) {
        $headers = array_shift($rows);
        echo "Headers in exported file:\n";
        print_r($headers);
        
        if (!empty($rows)) {
            $firstRow = reset($rows);
            echo "First row data:\n";
            print_r($firstRow);
        } else {
            echo "No data rows exported.\n";
        }
    } else {
        echo "Exported file is empty.\n";
    }
    
    $spreadsheet->disconnectWorksheets();
    unlink($filePath);
} else {
    echo "Failed to create exported file.\n";
}
