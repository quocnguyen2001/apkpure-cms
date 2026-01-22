<?php

namespace Wallis\ApkpureCrawler\DTOs\Apkpure;

final readonly class AppDTO
{
    public function __construct(
        public string $name,
        public ?string $logo,
        public array $images,
        public ?string $description,
        public ?string $content,
        public ?string $requiresAndroidOs,
        public ?string $lastedUpdate,
        public string $platform,
        public ?string $googlePlay,
        public string $category,
        public array $tags,
    ) {
    }
}
