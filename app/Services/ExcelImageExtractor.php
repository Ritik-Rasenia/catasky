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
     * Returns an array mapping "colLetter_rowNum" to the filename, e.g., ["P_2" => "img_xyz.png"]
     */
    public static function extract(string $filePath, string $destDir): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $drawings = $sheet->getDrawingCollection();
            $extracted = [];

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
                        if ($path && file_exists($path)) {
                            $imageContents = file_get_contents($path);
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
                        
                        // Store the mapping
                        $extracted[$key] = $fileName;
                    }
                }
            }

            // Clean up worksheets memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $extracted;
        } catch (\Throwable $e) {
            // Log or report error
            \Illuminate\Support\Facades\Log::error('ExcelImageExtractor Error: ' . $e->getMessage());
            return [];
        }
    }
}
