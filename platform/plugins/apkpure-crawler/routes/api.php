<?php

use Illuminate\Support\Facades\Route;
use Wallis\ApkpureCrawler\Http\Controllers\Api\ScraperController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/scraper',
], function (): void {
    Route::post('/', [ScraperController::class, 'scrape']);
    Route::get('{job}', [ScraperController::class, 'status']);
    Route::post('{job}/extract', [ScraperController::class, 'extract']);
});
