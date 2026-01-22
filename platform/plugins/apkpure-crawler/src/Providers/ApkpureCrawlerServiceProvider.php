<?php

namespace Wallis\ApkpureCrawler\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
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
            ->loadRoutes(['api'])
            ->loadAndPublishConfigurations(['apkpure', 'scraper', 'media'])
            ->loadMigrations();

        $this->app->register(CommandServiceProvider::class);
    }
}
