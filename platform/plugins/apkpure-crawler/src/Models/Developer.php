<?php

namespace Wallis\ApkpureCrawler\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string|null $website
 * @property string|null $logo
 * @property string|null $description
 * @property string|null $content
 * @property Collection<int, App> $apps
 */
class Developer extends AbstractModel
{
    protected $table = 'ac_developers';

    protected $fillable = [
        'name',
        'website',
        'logo',
        'description',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    public function apps(): HasMany
    {
        return $this->hasMany(App::class);
    }
}
