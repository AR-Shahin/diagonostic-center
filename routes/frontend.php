<?php

use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ReportController;
use App\Http\Controllers\Frontend\RequestController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website routes
|--------------------------------------------------------------------------
| Route names are prefixed with "site." so they never clash with the admin
| ("admin.") or the Breeze user ("login", "dashboard") routes.
*/
Route::middleware(SetLocale::class)->name('site.')->controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('about', 'about')->name('about');
    Route::get('tests', 'tests')->name('tests');
    Route::get('packages', 'packages')->name('packages');
    Route::get('doctors', 'doctors')->name('doctors');
    Route::get('services', 'services')->name('services');
    Route::get('contact', 'contact')->name('contact');
    Route::get('book-appointment', 'appointment')->name('appointment');
    Route::get('download-report', 'report')->name('report');
    Route::get('health-tips', 'articles')->name('articles');
    Route::get('health-tips/{slug}', 'article')->name('article');
    Route::get('sitemap.xml', 'sitemap')->name('sitemap');
});

// Website forms → Admin → Website requests. Limited per visitor to stop floods.
Route::middleware([SetLocale::class, 'throttle:6,1'])->name('site.')->controller(RequestController::class)->group(function () {
    Route::post('book-appointment', 'appointment')->name('appointment.store');
    Route::post('contact', 'contact')->name('contact.store');
    Route::post('corporate-quote', 'corporate')->name('corporate.store');
});

// Reports: invoice number + PIN download, and the QR "is this genuine?" check.
Route::middleware(SetLocale::class)->controller(ReportController::class)->group(function () {
    Route::post('download-report', 'lookup')->middleware('throttle:10,1')->name('report.lookup');
    Route::get('download-report/{invoice}', 'show')->name('report.show');
    Route::get('verify/{token}', 'verify')->name('report.verify');
});
