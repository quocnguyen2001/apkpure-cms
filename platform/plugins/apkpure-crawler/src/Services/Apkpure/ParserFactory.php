<?php

namespace Wallis\ApkpureCrawler\Services\Apkpure;

use InvalidArgumentException;
use Wallis\ApkpureCrawler\Services\Apkpure\Contracts\ParserInterface;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\AppDetailParser;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\AppVersionsPageParser;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\CategoryPageParser;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\HomePageParser;

final class ParserFactory
{
    public static function make(string $type): ParserInterface
    {
        return match ($type) {
            'detail' => new AppDetailParser(),
            'versions' => new AppVersionsPageParser(),
            'category' => new CategoryPageParser(),
            'home' => new HomePageParser(),
            default => throw new InvalidArgumentException("Unknown parser type: {$type}"),
        };
    }

    public static function makeFromHtml(string $html): ParserInterface
    {
        if (str_contains($html, 'class="app-info"') && str_contains($html, 'class="version-list"')) {
            return new AppDetailParser();
        }

        if (str_contains($html, 'class="aegon-down-item-btn"') || str_contains($html, 'data-pkg')) {
            return new AppVersionsPageParser();
        }

        if (str_contains($html, 'class="app-item"') && str_contains($html, 'class="paging"')) {
            return new CategoryPageParser();
        }

        if (str_contains($html, 'a.apk')) {
            return new HomePageParser();
        }

        throw new InvalidArgumentException('Cannot determine parser type from HTML content');
    }

    /**
     * @return array<int, string>
     */
    public static function availableTypes(): array
    {
        return ['detail', 'versions', 'category', 'home'];
    }
}
