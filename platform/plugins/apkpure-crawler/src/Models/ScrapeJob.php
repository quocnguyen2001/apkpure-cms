<?php

namespace Wallis\ApkpureCrawler\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $url
 * @property string $status
 * @property array|null $options
 * @property array|null $result
 * @property string|null $error_message
 * @property int $retry_count
 * @property CarbonInterface|null $started_at
 * @property CarbonInterface|null $completed_at
 * @property ScrapedContent|null $content
 */
class ScrapeJob extends AbstractModel
{
    protected $fillable = [
        'url',
        'status',
        'options',
        'result',
        'error_message',
        'retry_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'result' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function content(): HasOne
    {
        return $this->hasOne(ScrapedContent::class);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $result): void
    {
        $this->update([
            'status' => 'completed',
            'result' => $result,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }
}
