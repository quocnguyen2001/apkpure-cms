<?php

namespace Wallis\ApkpureCrawler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Wallis\ApkpureCrawler\Models\ScrapedContent;
use Wallis\ApkpureCrawler\Models\ScrapeJob;

class ScrapeCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ScrapeJob $job,
        public ScrapedContent $content
    ) {
    }
}
