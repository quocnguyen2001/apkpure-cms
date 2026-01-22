<?php

namespace Wallis\ApkpureCrawler\DTOs\Apkpure;

final readonly class AppCategoryDTO
{
    public function __construct(
        public string $slug,
        public string $name,
        public ?string $description,
        public ?string $content,
        public ?string $logo,
        public ?string $scrapeRefId,
    ) {
    }
}
