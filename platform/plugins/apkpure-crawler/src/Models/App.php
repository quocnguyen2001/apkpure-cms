<?php

namespace Wallis\ApkpureCrawler\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Wallis\ApkpureCrawler\Enums\AppPlatformEnum;

/**
 * @property string $name
 * @property string|null $logo
 * @property string|null $images
 * @property string|null $description
 * @property string|null $content
 * @property string|null $requires_android_os
 * @property CarbonInterface|null $lasted_update
 * @property AppPlatformEnum|string $platform
 * @property string|null $google_play
 * @property string|int|null $lasted_version_id
 * @property string|int|null $scrape_ref_id
 * @property string|int $developer_id
 * @property Developer $developer
 * @property Collection<int, AppVersion> $versions
 * @property AppVersion|null $lastedVersion
 * @property Collection<int, AppCategory> $categories
 * @property Collection<int, AppTag> $tags
 */
class App extends AbstractModel
{
    protected $table = 'ac_apps';

    protected $fillable = [
        'name',
        'logo',
        'images',
        'description',
        'content',
        'requires_android_os',
        'lasted_update',
        'platform',
        'google_play',
        'lasted_version_id',
        'scrape_ref_id',
        'developer_id',
    ];

    protected function casts(): array
    {
        return [
            'lasted_update' => 'date',
            'images' => 'array',
            'platform' => AppPlatformEnum::class,
        ];
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AppVersion::class);
    }

    public function lastedVersion(): BelongsTo
    {
        return $this->belongsTo(AppVersion::class, 'lasted_version_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(AppCategory::class, 'ac_app_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(AppTag::class, 'ac_app_tag');
    }
}
