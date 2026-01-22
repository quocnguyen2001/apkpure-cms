<?php

namespace Wallis\ApkpureCrawler\Services\Apkpure;

use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\AppDetailParser;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\AppVersionsPageParser;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\CategoryPageParser;
use Wallis\ApkpureCrawler\Services\Apkpure\Parsers\HomePageParser;

final readonly class ApkpureService
{
    public function __construct(
        private AppDetailParser $detailParser,
        private AppVersionsPageParser $versionsParser,
        private CategoryPageParser $categoryParser,
        private HomePageParser $homeParser,
    ) {
    }

    public function parseDetailPage(string $html): object
    {
        return $this->detailParser->parse($html);
    }

    public function parseVersionsPage(string $html): object
    {
        return $this->versionsParser->parse($html);
    }

    public function parseCategoryPage(string $html): object
    {
        return $this->categoryParser->parse($html);
    }

    /**
     * @return array<int, object>
     */
    public function parseHomePage(string $html): array
    {
        return $this->homeParser->parse($html);
    }

    public function parseAuto(string $html): object|array
    {
        $parser = ParserFactory::makeFromHtml($html);

        return $parser->parse($html);
    }
}
