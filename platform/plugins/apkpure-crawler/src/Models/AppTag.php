<?php

namespace Wallis\ApkpureCrawler\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $content
 * @property string|null $logo
 * @property Collection<int, App> $apps
 */
class AppTag extends AbstractModel
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
        'logo',
    ];

    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'app_tag');
    }
}
