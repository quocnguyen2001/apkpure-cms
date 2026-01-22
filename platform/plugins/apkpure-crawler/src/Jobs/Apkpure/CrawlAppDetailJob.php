<?php

namespace Wallis\ApkpureCrawler\Jobs\Apkpure;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Models\Developer;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\AppDetailParser;
use Wallis\ApkpureCrawler\Services\Scraper\ScraperService;

final class CrawlAppDetailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $url,
        public bool $force = false,
    ) {
    }

    public function handle(ScraperService $scraperService): void
    {
        $scrapedContent = $scraperService->getOrScrapeContent($this->url, $this->force);

        $parser = new AppDetailParser();
        $detailData = $parser->parse($scrapedContent->html);

        $developer = Developer::query()->firstOrCreate(
            ['slug' => $detailData->developer->slug],
            [
                'name' => $detailData->developer->name,
                'website' => $detailData->developer->website,
                'logo' => $detailData->developer->logo,
                'description' => $detailData->developer->description,
                'content' => $detailData->developer->content,
            ]
        );

        $images = $detailData->app->images;
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [$images];
        }

        $app = App::query()->updateOrCreate(
            ['scrape_ref_id' => $scrapedContent->id],
            [
                'name' => $detailData->app->name,
                'logo' => $detailData->app->logo,
                'images' => $images,
                'description' => $detailData->app->description,
                'content' => $detailData->app->content,
                'requires_android_os' => $detailData->app->requiresAndroidOs,
                'lasted_update' => $detailData->app->lastedUpdate,
                'platform' => $detailData->app->platform,
                'google_play' => $detailData->app->googlePlay,
                'developer_id' => $developer->id,
            ]
        );

        CrawlAppVersionsJob::dispatch($app->id, $this->force);
    }
}
