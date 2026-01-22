<?php

namespace Wallis\ApkpureCrawler\Jobs\Apkpure;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Wallis\ApkpureCrawler\Models\App;
use Wallis\ApkpureCrawler\Models\AppVersion;
use Wallis\ApkpureCrawler\Models\ScrapedContent;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\AppVersionsPageParser;
use Wallis\ApkpureCrawler\Services\Scraper\ScraperService;

final class CrawlAppVersionsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $appId,
        public bool $force = false,
    ) {
    }

    public function handle(ScraperService $scraperService): void
    {
        $app = App::query()->findOrFail($this->appId);

        $scrapedContent = ScrapedContent::query()->findOrFail($app->scrape_ref_id);

        $versionsUrl = rtrim($scrapedContent->url, '/') . '/versions';
        $scrapedContent = $scraperService->getOrScrapeContent($versionsUrl, $this->force);

        $parser = new AppVersionsPageParser();
        $versions = $parser->parse($scrapedContent->html);

        foreach ($versions as $versionData) {
            AppVersion::query()->updateOrCreate(
                [
                    'app_id' => $app->id,
                    'version' => $versionData->version,
                ],
                [
                    'changelog' => $versionData->changelog,
                    'release_date' => $versionData->releaseDate,
                    'file_size' => $versionData->fileSize,
                    'file_path' => $versionData->directDownloadUrl ?? $versionData->originDownloadUrl,
                    'storage_disk' => config('plugins.apkpure-crawler.scraper.storage.disk', 'local'),
                    'origin_download_url' => $versionData->originDownloadUrl,
                    'scrape_ref_id' => $scrapedContent->id,
                ]
            );
        }

        $latestVersion = $app->versions()->latest('release_date')->first();

        if ($latestVersion) {
            $app->update(['lasted_version_id' => $latestVersion->id]);
        }
    }
}
