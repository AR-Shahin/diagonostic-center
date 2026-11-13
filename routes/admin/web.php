<?php

use App\Helper\Trait\Helper;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LabController;
use App\Http\Controllers\Admin\LabTestController;
use App\Http\Controllers\Admin\ManualController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReferrerController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SampleTypeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UnitController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/manual', ManualController::class)->name('manual');

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

    // ---- Phase 1: setup & catalog
    Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('sample-types', SampleTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('lab-tests', LabTestController::class)->except(['show']);
    Route::resource('packages', PackageController::class)->except(['show']);

    Route::get('business-settings', [BusinessSettingController::class, 'index'])->name('business-settings.index');
    Route::post('business-settings', [BusinessSettingController::class, 'update'])->name('business-settings.update');

    // ---- Phase 2: patients & billing
    Route::get('patients/search', [PatientController::class, 'search'])->name('patients.search');
    Route::post('patients/{patient}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset-password');
    Route::resource('patients', PatientController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('referrers/search', [ReferrerController::class, 'search'])->name('referrers.search');
    Route::resource('referrers', ReferrerController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::controller(InvoiceController::class)->prefix('invoices')->name('invoices.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('', 'store')->name('store');
        Route::get('items', 'items')->name('items');
        Route::get('receipts/{payment}/print', 'receipt')->name('receipt');
        Route::get('{invoice}', 'show')->name('show');
        Route::get('{invoice}/print', 'print')->name('print');
        Route::post('{invoice}/collect', 'collect')->name('collect');
        Route::post('{invoice}/cancel', 'cancel')->name('cancel');
    });

    // ---- Phase 3: lab
    Route::controller(LabController::class)->prefix('lab')->name('lab.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('scan', 'scan')->name('scan');
        Route::get('invoices/{invoice}', 'invoice')->name('invoice');
        Route::get('invoices/{invoice}/labels', 'labels')->name('labels');
        Route::get('invoices/{invoice}/report', 'report')->name('report');
        Route::post('invoices/{invoice}/verify-all', 'verifyAll')->name('verify-all');
        Route::post('invoices/{invoice}/deliver', 'deliver')->name('deliver');
        Route::post('samples/{sample}/collect', 'collect')->name('collect');
        Route::post('samples/{sample}/receive', 'receive')->name('receive');
        Route::post('samples/{sample}/reject', 'reject')->name('reject');
        Route::get('items/{item}', 'edit')->name('result');
        Route::post('items/{item}', 'save')->name('save');
        Route::post('items/{item}/verify', 'verify')->name('verify');
        Route::post('items/{item}/amend', 'amend')->name('amend');
    });
});
