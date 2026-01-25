<?php

namespace Wallis\ApkpureCrawler\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Language\Facades\Language;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Models\AppCategory;
use Wallis\ApkpureCrawler\Models\AppTag;
use Wallis\ApkpureCrawler\Models\AppVersion;
use Wallis\ApkpureCrawler\Models\Developer;
use Wallis\ApkpureCrawler\Services\Media\MediaManager;
use Wallis\ApkpureCrawler\Services\Scraper\ContentParser;
use Wallis\ApkpureCrawler\Services\Scraper\PlaywrightClient;
use Wallis\ApkpureCrawler\Services\Scraper\ScraperService;

class ApkpureCrawlerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(PlaywrightClient::class, function (): PlaywrightClient {
            return new PlaywrightClient(
                config('plugins.apkpure-crawler.scraper.playwright.host', 'localhost'),
                (int) config('plugins.apkpure-crawler.scraper.playwright.port', 3000)
            );
        });

        $this->app->singleton(ContentParser::class, function (): ContentParser {
            return new ContentParser();
        });

        $this->app->singleton(ScraperService::class, function ($app): ScraperService {
            return new ScraperService(
                $app->make(PlaywrightClient::class),
                $app->make(ContentParser::class)
            );
        });

        $this->app->singleton(MediaManager::class, function ($app): MediaManager {
            return new MediaManager(
                filesystem: $app->make(FilesystemFactory::class)
            );
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/apkpure-crawler')
            ->loadRoutes(['api', 'web'])
            ->loadAndPublishConfigurations(['apkpure', 'scraper', 'media', 'permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadMigrations()
            ->publishAssets();

        SlugHelper::registering(function (): void {
            SlugHelper::registerModule(AppCategory::class, fn () => trans('plugins/apkpure-crawler::app-categories.menu_name'));
            SlugHelper::registerModule(AppTag::class, fn () => trans('plugins/apkpure-crawler::app-tags.menu_name'));
            SlugHelper::registerModule(Developer::class, fn () => trans('plugins/apkpure-crawler::developers.menu_name'));

            SlugHelper::setPrefix(AppCategory::class, 'app-categories', true);
            SlugHelper::setPrefix(AppTag::class, 'app-tags', true);
            SlugHelper::setPrefix(Developer::class, 'developers', true);
        });

        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-apkpure-crawler')
                        ->priority(380)
                        ->name('plugins/apkpure-crawler::apps.menu_name')
                        ->icon('ti ti-brand-android')
                        ->route('apkpure-crawler.apps.index')
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-apkpure-crawler-app-categories')
                        ->priority(381)
                        ->name('plugins/apkpure-crawler::app-categories.menu_name')
                        ->icon('ti ti-folders')
                        ->route('apkpure-crawler.app-categories.index')
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-apkpure-crawler-app-tags')
                        ->priority(382)
                        ->name('plugins/apkpure-crawler::app-tags.menu_name')
                        ->icon('ti ti-tags')
                        ->route('apkpure-crawler.app-tags.index')
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-apkpure-crawler-developers')
                        ->priority(383)
                        ->name('plugins/apkpure-crawler::developers.menu_name')
                        ->icon('ti ti-users')
                        ->route('apkpure-crawler.developers.index')
                );
        });

        if (defined('LANGUAGE_MODULE_SCREEN_NAME') && defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            LanguageAdvancedManager::registerModule(App::class, [
                'description',
                'content',
            ]);

            LanguageAdvancedManager::registerModule(AppCategory::class, [
                'description',
                'content',
            ]);

            LanguageAdvancedManager::registerModule(AppTag::class, [
                'description',
                'content',
            ]);

            LanguageAdvancedManager::registerModule(AppVersion::class, [
                'changelog',
            ]);

            LanguageAdvancedManager::registerModule(Developer::class, [
                'description',
                'content',
            ]);
        } elseif (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            Language::registerModule([
                App::class,
                AppCategory::class,
                AppTag::class,
                AppVersion::class,
                Developer::class,
            ]);
        }

        $this->app->register(CommandServiceProvider::class);
    }
}
