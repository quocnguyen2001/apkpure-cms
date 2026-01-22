<?php

namespace Wallis\ApkpureCrawler\Services\Scraper;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Wallis\ApkpureCrawler\Events\ScrapeCompleted;
use Wallis\ApkpureCrawler\Events\ScrapeFailed;
use Wallis\ApkpureCrawler\Models\ScrapedContent;
use Wallis\ApkpureCrawler\Models\ScrapeJob;

class ScraperService
{
    public function __construct(
        private PlaywrightClient $client,
        private ContentParser $parser
    ) {
    }

    public function scrape(string $url, array $options = []): ScrapeJob
    {
        $job = ScrapeJob::query()->create([
            'url' => $url,
            'options' => $options,
            'status' => 'pending',
        ]);

        try {
            $job->markAsProcessing();

            $result = $this->client->scrape($url, $options);

            $content = $this->saveContent($job, $result);

            $job->markAsCompleted($result);

            event(new ScrapeCompleted($job, $content));

            return $job->fresh();
        } catch (\Exception $exception) {
            $job->markAsFailed($exception->getMessage());

            event(new ScrapeFailed($job, $exception));

            $retryTimes = (int) config('plugins.apkpure-crawler.scraper.retry.times', 3);

            if ($job->retry_count < $retryTimes) {
                $job->increment('retry_count');
            }

            throw $exception;
        }
    }

    private function saveContent(ScrapeJob $job, array $result): ScrapedContent
    {
        $screenshotPath = null;

        if (isset($result['screenshot'])) {
            $screenshotPath = $this->saveScreenshot(
                $result['screenshot'],
                (string) $job->id
            );
        }

        return ScrapedContent::query()->create([
            'scrape_job_id' => $job->id,
            'url' => $result['url'],
            'title' => $result['title'] ?? null,
            'html' => $result['html'],
            'cookies' => $result['cookies'] ?? [],
            'metadata' => [
                'original_url' => $result['originalUrl'],
                'timestamp' => $result['timestamp'],
                'script_result' => $result['scriptResult'] ?? null,
            ],
            'screenshot_path' => $screenshotPath,
        ]);
    }

    private function saveScreenshot(string $base64Data, string $jobId): string
    {
        $filename = "screenshots/{$jobId}-" . Str::random(10) . '.png';

        Storage::disk(config('plugins.apkpure-crawler.scraper.storage.disk', 'local'))
            ->put($filename, base64_decode($base64Data));

        return $filename;
    }

    public function getOrScrapeContent(string $url, bool $force = false, array $options = []): ScrapedContent
    {
        if (! $force) {
            $existingContent = ScrapedContent::query()
                ->where('url', $url)
                ->latest()
                ->first();

            if ($existingContent) {
                return $existingContent;
            }
        }

        $job = $this->scrape($url, $options);

        return $job->content;
    }

    public function extractData(ScrapedContent $content, array $selectors): array
    {
        return $this->parser->extract($content->html, $selectors);
    }
}
