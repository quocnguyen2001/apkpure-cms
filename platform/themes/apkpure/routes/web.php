<?php

use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;
use Theme\Apkpure\Http\Controllers\ApkpureController;

Theme::registerRoutes(function (): void {
    Route::get('apps', [ApkpureController::class, 'getApps'])->name('public.apps');
    Route::get('games', [ApkpureController::class, 'getGames'])->name('public.games');
    Route::get('search', [ApkpureController::class, 'getSearch'])->name('public.search');
    Route::get('app/{slug}', [ApkpureController::class, 'getAppDetail'])->name('public.app.detail');
    Route::get('app/{slug}/versions', [ApkpureController::class, 'getAppVersions'])->name('public.app.versions');
    Route::get('category/{slug}', [ApkpureController::class, 'getCategory'])->name('public.category');
});

Theme::routes();
