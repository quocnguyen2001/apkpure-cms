<?php

namespace Wallis\ApkpureCrawler\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $scrape_job_id
 * @property string $url
 * @property string|null $title
 * @property string $html
 * @property array|null $cookies
 * @property array|null $metadata
 * @property string|null $screenshot_path
 * @property ScrapeJob $job
 */
class ScrapedContent extends AbstractModel
{
    protected $fillable = [
        'scrape_job_id',
        'url',
        'title',
        'html',
        'cookies',
        'metadata',
        'screenshot_path',
    ];

    protected function casts(): array
    {
        return [
            'cookies' => 'array',
            'metadata' => 'array',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ScrapeJob::class, 'scrape_job_id');
    }
}
