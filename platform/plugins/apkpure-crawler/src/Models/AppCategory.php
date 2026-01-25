<?php

namespace Wallis\ApkpureCrawler\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property string|null $content
 * @property string|null $logo
 * @property AppCategory|null $parent
 * @property Collection<int, AppCategory> $children
 * @property Collection<int, App> $apps
 */
class AppCategory extends AbstractModel
{
    protected $table = 'ac_app_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'content',
        'logo',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AppCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AppCategory::class, 'parent_id');
    }

    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'ac_app_category');
    }
}
