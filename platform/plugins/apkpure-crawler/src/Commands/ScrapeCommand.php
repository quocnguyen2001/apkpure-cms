<?php

namespace Wallis\ApkpureCrawler\Commands;

use Illuminate\Console\Command;
use Wallis\ApkpureCrawler\Services\Scraper\ScraperService;

class ScrapeCommand extends Command
{
    protected $signature = 'scraper:run
                            {url : The URL to scrape}
                            {--timeout=30000 : Timeout in milliseconds}
                            {--screenshot : Take a screenshot}
                            {--selector= : Wait for selector}
                            {--output= : Output file path}';

    protected $description = 'Scrape a URL using Playwright';

    public function handle(ScraperService $scraper): int
    {
        $url = $this->argument('url');

        $options = array_filter([
            'timeout' => (int) $this->option('timeout'),
            'screenshot' => $this->option('screenshot'),
            'waitForSelector' => $this->option('selector'),
        ]);

        $this->info("Scraping: {$url}");

        try {
            $job = $scraper->scrape($url, $options);

            $this->info('✓ Success!');
            $this->line("Job ID: {$job->id}");
            $this->line("Title: {$job->content->title}");
            $this->line('HTML length: ' . strlen($job->content->html) . ' bytes');

            if ($output = $this->option('output')) {
                file_put_contents($output, $job->content->html);
                $this->info("Saved to: {$output}");
            }

            return self::SUCCESS;
        } catch (\Exception $exception) {
            $this->error('✗ Failed: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }
}
