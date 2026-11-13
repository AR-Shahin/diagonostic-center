<?php

use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

// Public website (home, tests, packages, doctors, ...)
require __DIR__.'/frontend.php';

// Patient accounts (phone number login). Replaces the starter's email-based user login/register.
require __DIR__.'/portal.php';
require __DIR__.'/admin/auth.php';
require __DIR__.'/admin/web.php';

Route::post('/backup-download', [BackupController::class, 'downloadBackup'])->name('backup.download');
