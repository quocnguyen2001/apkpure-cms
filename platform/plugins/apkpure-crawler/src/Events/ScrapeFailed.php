<?php

namespace Wallis\ApkpureCrawler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Wallis\ApkpureCrawler\Models\ScrapeJob;

class ScrapeFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ScrapeJob $job,
        public \Throwable $exception
    ) {
    }
}
