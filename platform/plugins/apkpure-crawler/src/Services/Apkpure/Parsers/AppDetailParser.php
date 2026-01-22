<?php

namespace Wallis\ApkpureCrawler\Services\Apkpure\Parsers;

use Symfony\Component\DomCrawler\Crawler;
use Wallis\ApkpureCrawler\DTOs\Apkpure\AppDeveloperDTO;
use Wallis\ApkpureCrawler\DTOs\Apkpure\AppDTO;
use Wallis\ApkpureCrawler\Services\Apkpure\Contracts\ParserInterface;

final class AppDetailParser implements ParserInterface
{
    public function parse(string $html): object
    {
        $crawler = new Crawler($html);

        return (object) [
            'app' => $this->parseApp($crawler),
            'developer' => $this->parseDeveloper($crawler),
        ];
    }

    private function parseApp(Crawler $crawler): AppDTO
    {
        $title = $this->safeText($crawler, '.app-info .app-name-container span.one-line');
        $shortDescription = $this->safeText($crawler, 'h3.app-short-desc');
        $logoUrl = $this->safeAttr($crawler, '.app-info .app-icon img', 'src');
        $content = $this->safeHtml($crawler, '.show-more .show-more-content .description');

        $requiresAndroidOs = $this->safeText($crawler, 'li[data-dt-desc="AndroidOS"] .head');

        $images = [];

        $crawler->filter('.screenshots-container-warper .screenshots-list .screenshots-item')->each(function (Crawler $node) use (&$images): void {
            $images[] = $node->attr('data-original') ?? $node->attr('href');
        });

        $lastedUpdate = $crawler
            ->filterXPath('//li[.//div[@class="desc" and text()="Update date"]]//div[@class="head"]')
            ->text();

        $googlePlayUrl = $crawler
            ->filterXPath('//div[@class="additional-item"][.//p[contains(text(), "Available on")]]//a')
            ->attr('href');

        $category = $this->safeText($crawler, '.additional-content .additional-item .dt-category');
        $category = str_replace('Free ', '', $category);

        $tags = [];

        $crawler->filter('.tag-box .tag-item')->each(function (Crawler $node) use (&$tags): void {
            $tags[] = $node->text();
        });

        return new AppDTO(
            name: $title ?? 'Unknown App',
            logo: $logoUrl,
            images: $images,
            description: $shortDescription,
            content: $content,
            requiresAndroidOs: $requiresAndroidOs,
            lastedUpdate: $lastedUpdate,
            platform: 'android',
            googlePlay: $googlePlayUrl,
            category: $category,
            tags: $tags,
        );
    }

    private function parseDeveloper(Crawler $crawler): AppDeveloperDTO
    {
        $developerElement = $crawler->filter('.developer.one-line');

        if ($developerElement->count() === 0) {
            return new AppDeveloperDTO(
                name: 'Unknown Developer',
                slug: 'unknown',
                website: null,
                description: null,
                content: null,
            );
        }

        $developerName = $this->safeText($crawler, '.developer.one-line');
        $developerUrl = $this->safeAttr($crawler, '.developer.one-line', 'href');

        return new AppDeveloperDTO(
            name: $developerName ?? 'Unknown Developer',
            slug: $this->extractSlugFromUrl($developerUrl ?? ''),
            website: $developerUrl,
            description: null,
            content: null,
        );
    }

    private function extractSlugFromUrl(string $url): string
    {
        if ($url === '') {
            return 'unknown';
        }

        $parts = explode('/', trim($url, '/'));

        return end($parts) ?: 'unknown';
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

    private function safeHtml(Crawler $crawler, string $selector): ?string
    {
        try {
            $element = $crawler->filter($selector);

            return $element->count() > 0 ? $element->html() : null;
        } catch (\Exception) {
            return null;
        }
    }
}
