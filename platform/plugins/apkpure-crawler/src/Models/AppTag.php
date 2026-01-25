<?php

namespace Wallis\ApkpureCrawler\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $name
 * @property string|null $description
 * @property string|null $content
 * @property string|null $logo
 * @property Collection<int, App> $apps
 */
class AppTag extends AbstractModel
{
    protected $table = 'ac_app_tags';

    protected $fillable = [
        'name',
        'description',
        'content',
        'logo',
    ];

    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'ac_app_tag');
    }
}
