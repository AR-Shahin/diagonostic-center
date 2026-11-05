<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function downloadBackup()
    {
        try {
            Log::info('Database Backup started -- '.date('Y-m-d H:i:s'));
            $backupDirectory = storage_path('app/db_backup');
            if (File::exists($backupDirectory)) {
                File::deleteDirectory($backupDirectory);
            }

            Log::info('Call Artisan command -- '.date('Y-m-d H:i:s'));
            Artisan::call('backup:run');
            Log::info('Execute the Artisan Command -- '.date('Y-m-d H:i:s'));

            if (File::exists($backupDirectory)) {
                $files = File::files($backupDirectory);
                $zipFile = collect($files)->first(function ($file) {
                    return $file->getExtension() === 'zip';
                });

                return Response::download(
                    $zipFile->getRealPath()
                )->deleteFileAfterSend(true);
            }

            Log::info('Database Backup done!! -- '.date('Y-m-d H:i:s'));

            return response()->json(['error' => 'Backup file not found.'], 404);
        } catch (Exception $e) {
            Log::error('Database Backup Error -- '.date('Y-m-d H:i:s'));
            Log::error($e->getMessage());
        }
    }
}
