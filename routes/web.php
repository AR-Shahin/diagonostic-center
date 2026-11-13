<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public website (home, tests, packages, doctors, ...)
require __DIR__.'/frontend.php';

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
require __DIR__.'/admin/auth.php';
require __DIR__.'/admin/web.php';

Route::post('/backup-download', [BackupController::class, 'downloadBackup'])->name('backup.download');
