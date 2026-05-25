<?php

$dir = realpath(__DIR__ . '/../resources/views/subscriber-panel');
if (!$dir) {
    die("Directory subscriber-panel not found\n");
}

function processDirectory($path) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $count = 0;

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();
            $content = file_get_contents($filePath);
            
            $updated = false;
            // Handle both single and double quotes
            if (strpos($content, "@extends('admin.layouts.app')") !== false) {
                $content = str_replace("@extends('admin.layouts.app')", "@extends('subscriber-panel.layouts.app')", $content);
                $updated = true;
            } elseif (strpos($content, '@extends("admin.layouts.app")') !== false) {
                $content = str_replace('@extends("admin.layouts.app")', '@extends("subscriber-panel.layouts.app")', $content);
                $updated = true;
            }

            if ($updated) {
                file_put_contents($filePath, $content);
                echo "Updated layout in: " . basename($filePath) . " ($filePath)\n";
                $count++;
            }
        }
    }

    echo "\nTotal views updated: $count\n";
}

processDirectory($dir);
