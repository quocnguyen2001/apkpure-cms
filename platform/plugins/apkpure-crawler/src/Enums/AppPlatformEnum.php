<?php

namespace Wallis\ApkpureCrawler\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static AppPlatformEnum ANDROID()
 * @method static AppPlatformEnum IOS()
 */
class AppPlatformEnum extends Enum
{
    public const ANDROID = 'android';
    public const IOS = 'ios';

    public static $langPath = 'plugins/apkpure-crawler::apps.platforms';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::ANDROID => Html::tag('span', self::ANDROID()->label(), ['class' => 'badge bg-primary text-primary-fg']),
            self::IOS => Html::tag('span', self::IOS()->label(), ['class' => 'badge bg-info text-info-fg']),
            default => parent::toHtml(),
        };
    }
}
