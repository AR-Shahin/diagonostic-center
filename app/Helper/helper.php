<?php

use App\Helper\File\File as FileFile;
use App\Helper\Trait\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

function database_backup(Helper $helper)
{

    $backupDir = storage_path('backups');
    if (! is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    $directoryPath = storage_path('backups');
    $oldSQLFiles = File::glob("$directoryPath/*.sql");
    foreach ($oldSQLFiles as $file) {
        FileFile::deleteFile($file);
    }

    $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
    $backupFile = "{$backupDir}/backup_{$timestamp}.sql";
    if (DB::connection()->getDriverName() == 'sqlite') {
        $helper->warningAlert('Wrong Database!');

        return back();
    }
    $tables = DB::select('SHOW TABLES');
    $tableKey = key((array) $tables[0]);

    $file = fopen($backupFile, 'w');

    foreach ($tables as $table) {
        $tableName = $table->{$tableKey};

        // Get CREATE TABLE statement
        $createTable = DB::select("SHOW CREATE TABLE {$tableName}")[0]->{'Create Table'};

        // Write CREATE TABLE statement to the backup file
        fwrite($file, $createTable.";\n\n");

        // Get table data
        $data = DB::select("SELECT * FROM {$tableName}");

        if (count($data)) {
            // Insert data into backup file
            foreach ($data as $row) {
                $rowData = array_values((array) $row);
                $rowData = array_map(fn ($val) => is_null($val) ? 'NULL' : "'".addslashes($val)."'", $rowData);
                $rowDataStr = implode(', ', $rowData);
                fwrite($file, "INSERT INTO {$tableName} VALUES ({$rowDataStr});\n");
            }

            fwrite($file, "\n");
        }
    }

    fclose($file);

    return response()->download($backupFile);
}

function database_backup_with_file(Helper $helper)
{
    try {
        $directoryPath = storage_path('app/Laravel');

        $zipFiles = File::glob("$directoryPath/*.zip");
        foreach ($zipFiles as $zipFile) {

            $filename = pathinfo($zipFile, PATHINFO_FILENAME);

            $zipFileContents = Storage::get("Laravel/{$filename}.zip");

            unlink($zipFile);
        }

        $exitCode = Artisan::call('backup:run');
        if ($exitCode != 0) {

            $helper->errorAlert('Error!');

            return back();
        } else {
            $zipFiles = File::glob("$directoryPath/*.zip");

            if ($zipFiles) {
                return response()->download($zipFiles[0]);
            } else {

                $helper->warningAlert('Something went wrong!');

                return back();
            }
        }

    } catch (Exception $e) {
        Log::info('Backup Error '.$e->getMessage());
        dd($e->getMessage());
    }

}
