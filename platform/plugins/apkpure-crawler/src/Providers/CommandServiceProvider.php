<?php

namespace Wallis\ApkpureCrawler\Providers;

use Botble\Base\Supports\ServiceProvider;
use Wallis\ApkpureCrawler\Commands\ScrapeCommand;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ScrapeCommand::class,
        ]);
    }
}
