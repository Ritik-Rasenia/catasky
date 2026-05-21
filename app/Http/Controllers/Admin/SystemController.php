<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    public function index()
    {
        // Get some basic system info
        $databaseName = DB::connection()->getDatabaseName();
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';

        // Get logs
        $logPath = storage_path('logs/laravel.log');
        $logs = "";
        if (File::exists($logPath)) {
            $logs = File::get($logPath);
            // Get last 100 lines
            $lines = explode("\n", $logs);
            $logs = array_slice($lines, -100);
            $logs = implode("\n", $logs);
        }

        return view('admin.system.index', compact('databaseName', 'phpVersion', 'laravelVersion', 'serverSoftware', 'logs'));
    }

    public function runCommand(Request $request)
    {
        $command = $request->command;
        $allowedCommands = [
            'cache:clear' => 'Application Cache Cleared',
            'config:clear' => 'Configuration Cache Cleared',
            'route:clear' => 'Route Cache Cleared',
            'view:clear' => 'Compiled Views Cleared',
            'migrate' => 'Database Migrations Run Successfully',
            'optimize' => 'Application Optimized Successfully',
        ];

        if (!isset($allowedCommands[$command])) {
            return back()->with('error', 'Invalid Command');
        }

        try {
            Artisan::call($command);
            return back()->with('success', $allowedCommands[$command]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function storageLink()
    {
        try {
            Artisan::call('storage:link');
            return back()->with('success', 'Storage Link Created Successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function clearLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            if (File::exists($logPath)) {
                File::put($logPath, '');
            }
            return back()->with('success', 'System Logs Cleared Successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
