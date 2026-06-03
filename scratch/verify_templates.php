<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$app->make(Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\ProductController as AdminController;
use App\Http\Controllers\Subscriber\ProductController as SubscriberController;

function verifyTemplate($name, $controllerClass) {
    echo "==========================================\n";
    echo "Verifying Template for: $name\n";
    echo "==========================================\n";
    
    $controller = new $controllerClass();
    
    // Since downloadTemplate returns a download response, let's capture it.
    // However, to inspect the spreadsheet directly without exporting to a file and reading it back,
    // let's look at the generated file or mock the method's inner parts, or just run the method,
    // catch the downloaded file path from the Symfony response, and parse it using PhpOffice\PhpSpreadsheet\IOFactory!
    
    // Since downloadTemplate doesn't query the database, we can skip DB/User checks.
    // $user = \App\Models\User::first();
    // if ($user) {
    //     auth()->login($user);
    // }
    
    try {
        $response = $controller->downloadTemplate();
        $file = $response->getFile();
        $filePath = $file->getPathname();
        
        echo "Template generated at temp path: $filePath\n";
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        
        // 1. Check columns count on headers
        $headers = $rows[1];
        $colCount = count(array_filter($headers));
        echo "Headers count: $colCount\n";
        echo "Headers list: " . implode(', ', $headers) . "\n";
        
        if ($colCount !== 22) {
            echo "[-] ERROR: Columns count is $colCount, expected exactly 22.\n";
        } else {
            echo "[+] SUCCESS: Exactly 22 columns found.\n";
        }
        
        // 2. Check dummy records count
        $rowCount = count($rows) - 1; // subtract header row
        echo "Dummy records count: $rowCount\n";
        if ($rowCount !== 10) {
            echo "[-] ERROR: Dummy records count is $rowCount, expected exactly 10.\n";
        } else {
            echo "[+] SUCCESS: Exactly 10 dummy records found.\n";
        }
        
        // 3. Inspect some specific details of the 10 dummy records
        $samples = array_slice($rows, 1); // skip headers
        $idx = 1;
        foreach ($samples as $rowIndex => $row) {
            $prodName = $row['A'];
            $sku = $row['B'];
            $brand = $row['D'];
            $category = $row['E'];
            $subcategory = $row['F'];
            $mrp = $row['G'];
            $offerPrice = $row['H'];
            $desc = $row['M'];
            $img = $row['P'];
            $tags = $row['T'];
            
            echo "Product $idx ($prodName) -> Brand: '$brand', Cat: '$category', Subcat: '$subcategory', MRP: '$mrp', Offer: '$offerPrice'\n";
            $idx++;
        }
        
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
    } catch (\Throwable $e) {
        echo "[-] ERROR: Failed to verify template: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
}

verifyTemplate("Admin Product Import Template", AdminController::class);
verifyTemplate("Subscriber Product Import Template", SubscriberController::class);
