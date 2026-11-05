<?php

use App\Helper\Trait\Helper;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/backup', fn () => database_backup_with_file(new Helper))->name('backup');
    Route::post('/backup-db', fn () => database_backup(new Helper))->name('backup_db');
    // Role
    Route::prefix('roles')->controller(RoleController::class)->name('roles.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('/store/{role?}', 'storeAndUpdate')->name('store');
        Route::post('delete/{role}', 'delete')->name('delete');
        Route::get('assign-permissions/{role}', 'assignPermission')->name('assign_permission');
        Route::post('assign--permissions/{role}', 'assignPermissionStore')->name('assign__permission');
    });

    // Permission
    Route::prefix('permissions')->controller(PermissionController::class)->name('permissions.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('data_table', 'data_table')->name('data_table');
        Route::post('store/{permission?}', 'store')->name('store');
        Route::post('delete/{permission}', 'delete')->name('delete');
    });

    // Admin
    Route::prefix('admins')->controller(AdminController::class)->name('admins.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{admin}', 'edit')->name('edit');
        Route::get('data_table', 'data_table')->name('data_table');
        Route::post('store', 'store')->name('store');
        Route::post('update/{admin?}', 'update')->name('update');
        Route::post('delete/{admin}', 'delete')->name('delete');
    });

    // Category
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    Route::get('shahin', [BackupController::class, 'backupAndDownload']);
    Route::get('/download', function (Request $request) {
        $filePath = storage_path('app/'.$request->query('file'));

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        abort(404, 'File not found.');
    })->name('admin.download');
});
