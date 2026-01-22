<?php

namespace Wallis\ApkpureCrawler\Services\Media;

final class MediaService
{
    private static ?MediaManager $instance = null;

    public static function upload(mixed $file): MediaUploader
    {
        return self::manager()->upload($file);
    }

    public static function downloadFromUrl(string $url): MediaUploader
    {
        return self::manager()->downloadFromUrl($url);
    }

    private static function manager(): MediaManager
    {
        if (self::$instance === null) {
            self::$instance = app(MediaManager::class);
        }

        return self::$instance;
    }

    public static function clearInstance(): void
    {
        self::$instance = null;
    }
}
