<?php

namespace Wallis\ApkpureCrawler\DTOs\Apkpure;

final readonly class AppDeveloperDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $website,
        public ?string $description,
        public ?string $content,
    ) {
    }
}
