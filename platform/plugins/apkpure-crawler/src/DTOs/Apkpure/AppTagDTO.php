<?php

namespace Wallis\ApkpureCrawler\DTOs\Apkpure;

final readonly class AppTagDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $content,
        public ?string $logo,
        public ?string $scrapeRefId,
    ) {
    }
}
