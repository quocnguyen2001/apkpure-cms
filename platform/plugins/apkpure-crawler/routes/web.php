<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Wallis\ApkpureCrawler\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'apkpure-crawler/apps', 'as' => 'apkpure-crawler.apps.'], function (): void {
            Route::resource('', 'AppController')->parameters(['' => 'app']);
        });

        Route::group(['prefix' => 'apkpure-crawler/app-categories', 'as' => 'apkpure-crawler.app-categories.'], function (): void {
            Route::resource('', 'AppCategoryController')->parameters(['' => 'app_category']);
        });

        Route::group(['prefix' => 'apkpure-crawler/app-tags', 'as' => 'apkpure-crawler.app-tags.'], function (): void {
            Route::resource('', 'AppTagController')->parameters(['' => 'app_tag']);
        });

        Route::group(['prefix' => 'apkpure-crawler/developers', 'as' => 'apkpure-crawler.developers.'], function (): void {
            Route::resource('', 'DeveloperController')->parameters(['' => 'developer']);
        });

        Route::group(['prefix' => 'apkpure-crawler/app-versions', 'as' => 'apkpure-crawler.app-versions.'], function (): void {
            Route::resource('', 'AppVersionController')->except([
                'index',
            ])->parameters(['' => 'app_version']);

            Route::match(['GET', 'POST'], 'list/{id}', [
                'as' => 'index',
                'uses' => 'AppVersionController@index',
            ])->wherePrimaryKey();
        });
    });
});
