<?php

namespace Wallis\ApkpureCrawler\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|int $app_id
 * @property string $version
 * @property string|null $changelog
 * @property CarbonInterface $release_date
 * @property int $file_size
 * @property string $file_path
 * @property string $storage_disk
 * @property string $origin_download_url
 * @property string|int $scrape_ref_id
 * @property App $app
 */
class AppVersion extends AbstractModel
{
    protected $fillable = [
        'app_id',
        'version',
        'changelog',
        'release_date',
        'file_size',
        'file_path',
        'storage_disk',
        'origin_download_url',
        'scrape_ref_id',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'file_size' => 'integer',
        ];
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
