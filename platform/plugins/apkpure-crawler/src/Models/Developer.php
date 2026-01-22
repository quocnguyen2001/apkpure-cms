<?php

namespace Wallis\ApkpureCrawler\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $slug
 * @property string|null $website
 * @property string|null $logo
 * @property string|null $description
 * @property string|null $content
 * @property Collection<int, App> $apps
 */
class Developer extends AbstractModel
{
    protected $fillable = [
        'name',
        'slug',
        'website',
        'logo',
        'description',
        'content',
    ];

    public function apps(): HasMany
    {
        return $this->hasMany(App::class);
    }
}
