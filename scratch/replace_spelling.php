<?php

$filesToUpdate = [
    "resources/views/layouts/frontend.blade.php",
    "resources/views/welcome.blade.php",
    "resources/views/c_catalogue.blade.php",
    "resources/views/pricing.blade.php",
    "resources/views/contact.blade.php",
    "resources/views/search-results.blade.php",
    "resources/views/store-contact.blade.php",
    "resources/views/category-products.blade.php",
    "resources/views/product-details.blade.php"
];

$replacements = [
    "catalogues" => "catalogs",
    "Catalogues" => "Catalogs",
    "catalogue" => "catalog",
    "Catalogue" => "Catalog",
    "CATALOGUE" => "CATALOG"
];

function shouldSkip($content, $match, $start, $end) {
    $precedingStart = max(0, $start - 15);
    $preceding = substr($content, $precedingStart, $start - $precedingStart);
    if (strpos($preceding, "route('") !== false || strpos($preceding, 'route("') !== false || substr($preceding, -2) === "c_" || substr($preceding, -11) === "generatePDF") {
        return true;
    }
    $following = substr($content, $end, 10);
    if (strpos($following, "_code") === 0 || strpos($following, "_urls") === 0 || strpos($following, "_path") === 0) {
        return true;
    }
    return false;
}

foreach ($filesToUpdate as $relPath) {
    $path = "c:\\xampp\\htdocs\\catasky\\" . $relPath;
    if (!file_exists($path)) {
        echo "File not found: {$path}\n";
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    $pattern = '/\b(catalogues|Catalogues|catalogue|Catalogue|CATALOGUE)\b/';
    
    if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        $matches = array_reverse($matches[0]);
        foreach ($matches as $matchInfo) {
            $word = $matchInfo[0];
            $offset = $matchInfo[1];
            $endOffset = $offset + strlen($word);
            
            if (shouldSkip($content, $word, $offset, $endOffset)) {
                continue;
            }
            
            $newVal = $replacements[$word];
            $content = substr_replace($content, $newVal, $offset, strlen($word));
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: {$relPath}\n";
    } else {
        echo "No changes: {$relPath}\n";
    }
}
