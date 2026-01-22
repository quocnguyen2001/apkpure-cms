<?php

namespace Wallis\ApkpureCrawler\Services\Scraper;

use DOMDocument;
use DOMXPath;

class ContentParser
{
    public function extract(string $html, array $selectors): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        $results = [];

        foreach ($selectors as $key => $query) {
            if (str_starts_with($query, '//')) {
                $results[$key] = $this->extractXPath($xpath, $query);
            } else {
                $xpathQuery = $this->cssToXPath($query);
                $results[$key] = $this->extractXPath($xpath, $xpathQuery);
            }
        }

        return $results;
    }

    private function extractXPath(DOMXPath $xpath, string $query): array
    {
        $nodes = $xpath->query($query);
        $result = [];

        foreach ($nodes as $node) {
            $result[] = $node->textContent;
        }

        return $result;
    }

    private function cssToXPath(string $css): string
    {
        $css = str_replace('.', "[@class='", $css);
        $css = str_replace('#', "[@id='", $css);

        if (str_contains($css, '[@')) {
            $css .= "']";
        }

        return "//{$css}";
    }
}
