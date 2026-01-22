<?php

namespace Wallis\ApkpureCrawler\Services\Media\Concerns;

use Illuminate\Support\Facades\Http;
use Wallis\ApkpureCrawler\Services\Media\Exceptions\DownloadFailedException;

trait HandlesUrls
{
    private function downloadFileContent(): string
    {
        if ($this->type !== 'download') {
            throw new \LogicException('Can only download when type is "download"');
        }

        $response = Http::timeout(config('plugins.apkpure-crawler.media.download.timeout', 30))
            ->retry((int) config('plugins.apkpure-crawler.media.download.max_retries', 3), 100)
            ->withUserAgent(config('plugins.apkpure-crawler.media.download.user_agent', 'MediaService/1.0'))
            ->get($this->file);

        if ($response->failed()) {
            throw new DownloadFailedException(
                "Failed to download file from {$this->file}: HTTP {$response->status()}"
            );
        }

        return $response->body();
    }
}
