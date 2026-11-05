<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ProfileController;
use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // $admin =  Admin::first();
    // return $admin->getAllPermissions()->pluck("name");
    return redirect()->route('admin.login');
});

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

Route::view('ars', 'admin.layouts.app')->name('ars');

Route::post('/backup-download', [BackupController::class, 'downloadBackup'])->name('backup.download');

Route::get('bal', [LoginController::class, 'create']);

Route::get('cache', function () {
    $admins = Cache::remember('admins', 10, function () {
        return Admin::all();
    });

    return view('cache', compact('admins'));
});
