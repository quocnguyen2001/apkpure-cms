<?php

namespace Wallis\ApkpureCrawler\Services\Scraper;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PlaywrightClient
{
    private string $baseUrl;

    private int $timeout;

    public function __construct(string $host, int $port)
    {
        $this->baseUrl = "http://{$host}:{$port}";
        $this->timeout = (int) config('plugins.apkpure-crawler.scraper.playwright.timeout', 30000);
    }

    public function scrape(string $url, array $options = []): array
    {
        $response = $this->client()
            ->post('/scrape', [
                'url' => $url,
                'options' => array_merge([
                    'timeout' => $this->timeout,
                ], $options),
            ]);

        if ($response->failed()) {
            throw new \Exception('Playwright scraping failed: ' . $response->body());
        }

        $data = $response->json();

        if (! $data['success']) {
            throw new \Exception($data['error']['message'] ?? 'Unknown error');
        }

        return $data['data'];
    }

    public function healthCheck(): bool
    {
        try {
            $response = $this->client()->get('/health');

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    private function client(): PendingRequest
    {
        return Http::timeout(120)
            ->retry(3, 1000)
            ->baseUrl($this->baseUrl);
    }
}
