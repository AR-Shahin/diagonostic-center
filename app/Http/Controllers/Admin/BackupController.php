<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    public function backupAndDownload()
    {
        // Step 1: Create a database backup
        // $databaseName = env('DB_DATABASE', 'laravel');
        // $databaseBackupFile = 'backups/' . date('Y-m-d_H-i-s') . '_database.sql';

        // Use Artisan command to create a database dump
        // Artisan::call("db:backup --database={$databaseName} --output={$databaseBackupFile}");

        // Step 2: Create a storage backup (as a ZIP file)
        $storageFolder = 'storage/';

        $storageBackupFile = 'backups/'.rand(1, 22).'_storage.zip';

        // Create a ZIP file for storage backup
        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/'.$storageBackupFile), ZipArchive::CREATE) === true) {
            $files = Storage::allFiles($storageFolder);
            foreach ($files as $file) {
                $zip->addFile(storage_path('app/'.$file), $file);
            }
            dd($zip);
            $zip->close();
        }

        // Step 3: Return files as downloadable responses
        $downloads = [
            // 'database' => storage_path('app/' . $databaseBackupFile),
            'storage' => storage_path('app/'.$storageBackupFile),
        ];

        // Provide download links for the user to download both files
        return response()->json([
            'message' => 'Backup completed.',
            'downloads' => [
                // 'database' => url('/admin/download?file=' . urlencode($databaseBackupFile)),
                'storage' => url('/admin/download?file='.urlencode($storageBackupFile)),
            ],
        ]);
    }
}
