<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Illuminate\Support\Str;

class ExcelImageExtractor
{
    /**
     * Extract drawings from a spreadsheet and save them to a directory.
     * Supports standard floating drawings and cell-embedded drawings from cellimages.xml.
     * Returns an array mapping "colLetter_rowNum" to the filename, e.g., ["L_2" => "cell_L2.png"]
     */
    public static function extract(string $filePath, string $destDir): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $extracted = [];
        $hasUnzipped = false;
        $unzipDir = $destDir . DIRECTORY_SEPARATOR . 'unzipped_' . Str::random(8);

        // 1. Unzip the spreadsheet to extract cell-embedded images (DISPIMG)
        try {
            @mkdir($unzipDir, 0755, true);
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $zip->extractTo($unzipDir);
                $zip->close();
                $hasUnzipped = true;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ExcelImageExtractor unzip failed: ' . $e->getMessage());
        }

        $nameToPath = [];
        if ($hasUnzipped) {
            // Casing can be case-sensitive on Linux (Hostinger). Locate files case-insensitively.
            $relsFile = self::findFileCaseInsensitive($unzipDir, 'xl/_rels/cellimages.xml.rels');
            $cellImagesFile = self::findFileCaseInsensitive($unzipDir, 'xl/cellimages.xml');
            
            $rels = [];
            if ($relsFile && file_exists($relsFile)) {
                $relsXml = @simplexml_load_file($relsFile);
                if ($relsXml) {
                    foreach ($relsXml->Relationship as $rel) {
                        $rId = (string)$rel['Id'];
                        $target = (string)$rel['Target'];
                        $rels[$rId] = $target;
                    }
                }
                
                // Regex fallback for rels XML
                if (empty($rels)) {
                    $relsContent = file_get_contents($relsFile);
                    preg_match_all('/<Relationship\b[^>]*Id\s*=\s*"([^"]+)"[^>]*Target\s*=\s*"([^"]+)"/i', $relsContent, $mRels);
                    if (!empty($mRels[1])) {
                        foreach ($mRels[1] as $idx => $rId) {
                            $rels[$rId] = $mRels[2][$idx];
                        }
                    }
                }
            }
            
            $cellImagesMap = [];
            if ($cellImagesFile && file_exists($cellImagesFile)) {
                $xmlContent = file_get_contents($cellImagesFile);
                
                $dom = new \DOMDocument();
                if (@$dom->loadXML($xmlContent)) {
                    $cellImageNodes = [];
                    $allNodes = $dom->getElementsByTagName('*');
                    foreach ($allNodes as $node) {
                        if (strtolower($node->localName) === 'cellimage') {
                            $cellImageNodes[] = $node;
                        }
                    }
                    
                    foreach ($cellImageNodes as $node) {
                        $name = '';
                        $embed = '';
                        
                        $childNodes = $node->getElementsByTagName('*');
                        foreach ($childNodes as $child) {
                            if (strtolower($child->localName) === 'cnvpr') {
                                $name = $child->getAttribute('name');
                            }
                            if (strtolower($child->localName) === 'blip') {
                                $embed = $child->getAttribute('r:embed');
                                if (!$embed) {
                                    $embed = $child->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'embed');
                                }
                                if (!$embed) {
                                    $embed = $child->getAttribute('embed');
                                }
                            }
                        }
                        
                        if ($name && $embed) {
                            $cellImagesMap[$name] = $embed;
                        }
                    }
                }
                
                // Regex fallback for cellimages XML
                if (empty($cellImagesMap)) {
                    preg_match_all('/<[^:>]*:?cellImage\b[^>]*>(.*?)<\/[^:>]*:?cellImage>/is', $xmlContent, $blocks);
                    foreach ($blocks[1] as $block) {
                        $name = '';
                        $embed = '';
                        if (preg_match('/\bname\s*=\s*"([^"]+)"/i', $block, $mName)) {
                            $name = $mName[1];
                        }
                        if (preg_match('/\b(?:r:)?embed\s*=\s*"([^"]+)"/i', $block, $mEmbed)) {
                            $embed = $mEmbed[1];
                        }
                        if ($name && $embed) {
                            $cellImagesMap[$name] = $embed;
                        }
                    }
                }
            }

            foreach ($cellImagesMap as $name => $embedId) {
                if (isset($rels[$embedId])) {
                    $nameToPath[$name] = $rels[$embedId];
                }
            }
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            // A. Extract standard floating drawings
            $drawings = $sheet->getDrawingCollection();
            foreach ($drawings as $drawing) {
                $coordinate = $drawing->getCoordinates(); // e.g. "P2"
                if (preg_match('/^([A-Z]+)(\d+)$/', $coordinate, $matches)) {
                    $col = $matches[1];
                    $row = (int)$matches[2];
                    $key = "{$col}_{$row}";

                    $extension = '';
                    $imageContents = '';

                    if ($drawing instanceof MemoryDrawing) {
                        $imageResource = $drawing->getImageResource();
                        if ($imageResource) {
                            ob_start();
                            $renderingFunction = $drawing->getRenderingFunction();
                            switch ($renderingFunction) {
                                case MemoryDrawing::RENDERING_JPEG:
                                    imagejpeg($imageResource);
                                    $extension = 'jpg';
                                    break;
                                case MemoryDrawing::RENDERING_GIF:
                                    imagegif($imageResource);
                                    $extension = 'gif';
                                    break;
                                case MemoryDrawing::RENDERING_PNG:
                                default:
                                    imagepng($imageResource);
                                    $extension = 'png';
                                    break;
                            }
                            $imageContents = ob_get_contents();
                            ob_end_clean();
                        }
                    } elseif ($drawing instanceof Drawing) {
                        $path = $drawing->getPath();
                        if ($path && (file_exists($path) || stripos($path, 'zip://') === 0)) {
                            $imageContents = @file_get_contents($path);
                            $extension = $drawing->getExtension();
                        }
                    }

                    if ($imageContents !== '') {
                        $extension = $extension ?: 'png';
                        $extension = strtolower(trim($extension));
                        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $extension = 'png';
                        }
                        $fileName = "row_{$row}_col_{$col}." . $extension;
                        $destPath = $destDir . DIRECTORY_SEPARATOR . $fileName;
                        file_put_contents($destPath, $imageContents);
                        
                        $extracted[$key] = $fileName;
                    }
                }
            }

            // B. Extract cell-embedded drawings from unzipped structures (DISPIMG)
            if (!empty($nameToPath)) {
                foreach ($sheet->getRowIterator() as $row) {
                    foreach ($row->getCellIterator() as $cell) {
                        $coordinate = $cell->getCoordinates();
                        $cellValue = (string)$cell->getValue(); // Cast to string to support RichText or numbers
                        if (stripos($cellValue, 'DISPIMG') !== false) {
                            if (preg_match('/DISPIMG\s*\(\s*["\']?([^"\',)]+)["\']?/i', $cellValue, $matches)) {
                                $imgId = $matches[1];
                                if (isset($nameToPath[$imgId])) {
                                    $relativePath = $nameToPath[$imgId];
                                    
                                    // Target file name can also have case sensitivity differences in the ZIP
                                    $unzippedPath = self::findFileCaseInsensitive($unzipDir, 'xl/' . $relativePath);
                                    if (!$unzippedPath) {
                                        $unzippedPath = self::findFileCaseInsensitive($unzipDir, $relativePath);
                                    }
                                    
                                    if ($unzippedPath && file_exists($unzippedPath)) {
                                        $ext = pathinfo($unzippedPath, PATHINFO_EXTENSION) ?: 'png';
                                        $fileName = "cell_{$coordinate}." . strtolower($ext);
                                        $destPath = $destDir . DIRECTORY_SEPARATOR . $fileName;
                                        @copy($unzippedPath, $destPath);
                                        
                                        if (preg_match('/^([A-Z]+)(\d+)$/', $coordinate, $coordMatches)) {
                                            $col = $coordMatches[1];
                                            $rowNum = $coordMatches[2];
                                            $extracted["{$col}_{$rowNum}"] = $fileName;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Clean up worksheets memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ExcelImageExtractor Error: ' . $e->getMessage());
        } finally {
            // Always clean up unzip directory
            if ($hasUnzipped) {
                self::deleteDir($unzipDir);
            }
        }

        return $extracted;
    }

    /**
     * Case-insensitive file path finder. Matches folders and files by ignoring case.
     */
    private static function findFileCaseInsensitive(string $baseDir, string $subPath): ?string
    {
        $parts = explode('/', str_replace('\\', '/', $subPath));
        $current = rtrim($baseDir, '/\\');
        
        foreach ($parts as $part) {
            if (!$part) continue;
            if (!is_dir($current)) {
                return null;
            }
            $items = scandir($current);
            $found = false;
            foreach ($items as $item) {
                if (strcasecmp($item, $part) === 0) {
                    $current = $current . DIRECTORY_SEPARATOR . $item;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return null;
            }
        }
        return file_exists($current) ? $current : null;
    }

    /**
     * Recursively delete a directory.
     */
    private static function deleteDir(string $dirPath): void
    {
        if (!is_dir($dirPath)) {
            return;
        }
        $files = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dirPath . '/' . $file;
            if (is_dir($filePath)) {
                self::deleteDir($filePath);
            } else {
                @unlink($filePath);
            }
        }
        @rmdir($dirPath);
    }
}
