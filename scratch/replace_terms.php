<?php

function replaceTermsInFile($filePath) {
    if (!file_exists($filePath)) {
        echo "File does not exist: $filePath\n";
        return;
    }
    
    $content = file_get_contents($filePath);
    
    // Ordered replacements to prevent partial overlapping issues
    $replacements = [
        // Table names & Columns
        'vendor_profiles' => 'subscriber_profiles',
        'vendor_products' => 'subscriber_products',
        'vendor_product_images' => 'subscriber_product_images',
        'vendor_product_attribute_values' => 'subscriber_product_attribute_values',
        'vendor_share_links' => 'subscriber_share_links',
        'vendor_pdf_templates' => 'subscriber_pdf_templates',
        'vendor_activity_logs' => 'subscriber_activity_logs',
        'vendor_product_id' => 'subscriber_product_id',
        
        // Paths / Uploads
        'vendor-logos' => 'subscriber-logos',
        'vendor-products' => 'subscriber-products',
        'vendor-panel' => 'subscriber-panel',
        
        // Relationships & camelCase / Methods
        'vendorProfile' => 'subscriberProfile',
        'vendorProducts' => 'subscriberProducts',
        'vendorActivityLog' => 'subscriberActivityLog',
        'vendorPdfTemplate' => 'subscriberPdfTemplate',
        'vendorProduct' => 'subscriberProduct',
        'vendorProductAttributeValue' => 'subscriberProductAttributeValue',
        'vendorProductImage' => 'subscriberProductImage',
        'vendorShareLink' => 'subscriberShareLink',
        'isVendor' => 'isSubscriber',
        
        // Standard Case Replacements
        'VENDOR' => 'SUBSCRIBER',
        'Vendor' => 'Subscriber',
        'vendor' => 'subscriber',
    ];
    
    $original = $content;
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        echo "Refactored: $filePath\n";
    }
}

// Recursively search and replace in a directory
function replaceTermsInDir($dirPath, $excludeDirs = ['vendor', 'node_modules', '.git', 'storage', 'bootstrap']) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $filePath = $fileInfo->getPathname();
            
            // Check if file is in excluded directories
            $exclude = false;
            foreach ($excludeDirs as $exDir) {
                if (str_contains($filePath, DIRECTORY_SEPARATOR . $exDir . DIRECTORY_SEPARATOR)) {
                    $exclude = true;
                    break;
                }
            }
            
            if (!$exclude) {
                replaceTermsInFile($filePath);
            }
        }
    }
}

// If argument is passed, refactor that specific file/directory, else run on whole app
if ($argc > 1) {
    $target = $argv[1];
    if (is_dir($target)) {
        replaceTermsInDir($target);
    } else {
        replaceTermsInFile($target);
    }
} else {
    echo "Please specify a target file or directory.\n";
}
