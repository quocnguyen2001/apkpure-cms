<?php

namespace Wallis\ApkpureCrawler\DTOs\Apkpure;

final readonly class AppVersionDTO
{
    public function __construct(
        public string $version,
        public string $versionCode,
        public ?string $changelog,
        public string $releaseDate,
        public int $fileSize,
        public string $originDownloadUrl,
        public ?string $directDownloadUrl,
    ) {
    }
}
