<?php

use App\Http\Controllers\Frontend\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website routes
|--------------------------------------------------------------------------
| Route names are prefixed with "site." so they never clash with the admin
| ("admin.") or the Breeze user ("login", "dashboard") routes.
*/
Route::name('site.')->controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('about', 'about')->name('about');
    Route::get('tests', 'tests')->name('tests');
    Route::get('packages', 'packages')->name('packages');
    Route::get('doctors', 'doctors')->name('doctors');
    Route::get('services', 'services')->name('services');
    Route::get('contact', 'contact')->name('contact');
    Route::get('book-appointment', 'appointment')->name('appointment');
    Route::get('download-report', 'report')->name('report');
    Route::get('sitemap.xml', 'sitemap')->name('sitemap');
});
