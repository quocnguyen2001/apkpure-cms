<?php

namespace Wallis\ApkpureCrawler\Services\Apkpure\Parsers;

use Symfony\Component\DomCrawler\Crawler;
use Wallis\ApkpureCrawler\Services\Apkpure\Contracts\ParserInterface;

final class HomePageParser implements ParserInterface
{
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);

        return $this->parseApps($crawler);
    }

    /**
     * @return array<int, object>
     */
    private function parseApps(Crawler $crawler): array
    {
        $apps = [];

        $crawler->filter('a.apk')->each(function (Crawler $node) use (&$apps): void {
            $name = $this->safeAttr($node, '', 'title', $node);
            $url = $this->safeAttr($node, '', 'href', $node);

            $apps[] = (object) [
                'name' => $name,
                'url' => $url,
            ];
        });

        return $apps;
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
