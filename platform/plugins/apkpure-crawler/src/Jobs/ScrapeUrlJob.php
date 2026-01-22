<?php

namespace Wallis\ApkpureCrawler\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Wallis\ApkpureCrawler\Models\ScrapeJob;
use Wallis\ApkpureCrawler\Services\Scraper\ScraperService;

class ScrapeUrlJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public ScrapeJob $job
    ) {
        $this->onConnection(config('plugins.apkpure-crawler.scraper.queue.connection', 'database'));
        $this->onQueue(config('plugins.apkpure-crawler.scraper.queue.name', 'scraper'));
    }

    public function handle(ScraperService $scraper): void
    {
        $scraper->scrape(
            $this->job->url,
            $this->job->options ?? []
        );
    }

    public function failed(\Throwable $exception): void
    {
        $this->job->markAsFailed($exception->getMessage());
    }
}
