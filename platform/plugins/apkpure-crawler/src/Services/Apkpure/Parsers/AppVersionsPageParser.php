<?php

namespace Wallis\ApkpureCrawler\Services\Apkpure\Parsers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Wallis\ApkpureCrawler\DTOs\Apkpure\AppVersionDTO;
use Wallis\ApkpureCrawler\Services\Apkpure\Contracts\ParserInterface;
use Wallis\ApkpureCrawler\Services\Scraper\ScraperService;

final class AppVersionsPageParser implements ParserInterface
{
    /**
     * @return array<int, AppVersionDTO>|object
     */
    public function parse(string $html): array|object
    {
        $crawler = new Crawler($html);

        $versions = [];

        $package = $this->safeAttr($crawler, '.versions-main', 'data-pkg');
        $downloadParams = $this->extractDownloadParams();

        $crawler->filter('.ver-wrap a.ver_download_link')->each(function (Crawler $node) use (&$versions, $package, $downloadParams): void {
            $versions[] = new AppVersionDTO(
                version: $node->attr('data-dt-version'),
                versionCode: $node->attr('data-dt-versioncode'),
                changelog: '',
                releaseDate: $node->filter('.ver-item .ver-item-info span.update-on')->text(),
                fileSize: (int) $node->attr('data-dt-filesize'),
                originDownloadUrl: $node->attr('href'),
                directDownloadUrl: isset($downloadParams['link_url'])
                    ? $this->transformDownloadUrl($downloadParams['link_url'], $package, $node->attr('data-dt-versioncode'))
                    : null
            );
        });

        return $versions;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractDownloadParams(): array
    {
        $downloadUrl = config('plugins.apkpure-crawler.apkpure.direct_download_url', '');
        $cacheKey = 'download_params_' . md5($downloadUrl);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($downloadUrl): array {
            try {
                if ($downloadUrl === '') {
                    return [];
                }

                $scraperService = app(ScraperService::class);
                $content = $scraperService->getOrScrapeContent($downloadUrl, true);

                $crawler = new Crawler($content->html);

                $downloadButton = $crawler->filter('.aegon-down-item-btn.dl-direct-download-btn');

                if ($downloadButton->count() === 0) {
                    return [];
                }

                $dtParams = $downloadButton->attr('dt-params');

                if (! $dtParams) {
                    return [];
                }

                parse_str($dtParams, $params);

                return $params;
            } catch (\Exception) {
                return [];
            }
        });
    }

    private function transformDownloadUrl(string $url, ?string $package, ?string $versionCode): string
    {
        $url = str_replace(
            'https://download.apkpure.com/',
            'https://d-e03.winudf.com/',
            $url
        );

        if ($package && $versionCode) {
            $url = Str::replaceMatches(
                '/com\.apkpure\.aegon-\d+\.apk\?/',
                sprintf('%s-%s.apk?/', $package, $versionCode),
                $url
            );
        }

        return $url;
    }

    private function extractFileSize(Crawler $crawler): ?int
    {
        try {
            $fileSizeText = $this->safeText($crawler, '.file-size');

            if (! $fileSizeText) {
                $fileSizeText = $this->safeText($crawler, '.apk-info .size');
            }

            if (! $fileSizeText) {
                return null;
            }

            return $this->convertFileSizeToBytes($fileSizeText);
        } catch (\Exception) {
            return null;
        }
    }

    private function convertFileSizeToBytes(string $sizeStr): ?int
    {
        $sizeStr = trim($sizeStr);

        if (preg_match('/^([\d.]+)\s*([KMGT]?B)$/i', $sizeStr, $matches)) {
            $size = (float) $matches[1];
            $unit = strtoupper($matches[2]);

            return match ($unit) {
                'B' => (int) $size,
                'KB' => (int) ($size * 1024),
                'MB' => (int) ($size * 1024 * 1024),
                'GB' => (int) ($size * 1024 * 1024 * 1024),
                'TB' => (int) ($size * 1024 * 1024 * 1024 * 1024),
                default => null,
            };
        }

        return null;
    }

    private function safeText(Crawler $crawler, string $selector): ?string
    {
        try {
            $element = $crawler->filter($selector);

            return $element->count() > 0 ? $element->text() : null;
        } catch (\Exception) {
            return null;
        }
    }

    private function safeAttr(Crawler $crawler, string $selector, string $attribute): ?string
    {
        try {
            $element = $crawler->filter($selector);
            if ($element->count() === 0) {
                return null;
            }

            return $element->attr($attribute);
        } catch (\Exception) {
            return null;
        }
    }
}
