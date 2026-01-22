<?php

namespace Wallis\ApkpureCrawler\Services\Apkpure\Parsers;

use Symfony\Component\DomCrawler\Crawler;
use Wallis\ApkpureCrawler\Services\Apkpure\Contracts\ParserInterface;

final class CategoryPageParser implements ParserInterface
{
    public function parse(string $html): object
    {
        $crawler = new Crawler($html);

        return (object) [
            'apps' => $this->parseApps($crawler),
            'pagination' => $this->parsePagination($crawler),
        ];
    }

    /**
     * @return array<int, object>
     */
    private function parseApps(Crawler $crawler): array
    {
        $apps = [];

        $crawler->filter('li.app-item a.app-icon')->each(function (Crawler $node) use (&$apps): void {
            $name = $this->safeAttr($node, '', 'title', $node);
            $url = $this->safeAttr($node, '', 'href', $node);
            $icon = $this->safeAttr($node, 'img', 'src');

            $apps[] = (object) [
                'name' => $name,
                'icon' => $icon,
                'url' => $url,
            ];
        });

        return $apps;
    }

    /**
     * @return array<int, string>
     */
    private function parsePagination(Crawler $crawler): array
    {
        $links = [];

        $crawler->filter('.paging li:not(.active) a')->each(function (Crawler $node) use (&$links): void {
            $href = $this->safeAttr($node, '', 'href', $node);
            if ($href) {
                $links[] = $href;
            }
        });

        $links = array_filter(array_unique($links));

        return array_values($links);
    }

    private function safeText(Crawler $crawler, string $selector, ?Crawler $node = null): ?string
    {
        try {
            $element = $selector ? $crawler->filter($selector) : $node;

            return $element && $element->count() > 0 ? $element->text() : null;
        } catch (\Exception) {
            return null;
        }
    }

    private function safeAttr(Crawler $crawler, string $selector, string $attribute, ?Crawler $node = null): ?string
    {
        try {
            $element = $selector ? $crawler->filter($selector) : $node;
            if (! $element || $element->count() === 0) {
                return null;
            }

            return $element->attr($attribute);
        } catch (\Exception) {
            return null;
        }
    }
}
