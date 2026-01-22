<?php

namespace Wallis\ApkpureCrawler\DTOs\Media;

use Carbon\CarbonInterface;

/**
 * @property string $path
 * @property string $fullPath
 * @property string|null $url
 * @property string $disk
 * @property int $size
 * @property string $mimeType
 * @property string $extension
 * @property string $hash
 * @property array $metadata
 * @property CarbonInterface $uploadedAt
 */
final readonly class MediaFileDTO
{
    public function __construct(
        public string $path,
        public string $fullPath,
        public ?string $url,
        public string $disk,
        public int $size,
        public string $mimeType,
        public string $extension,
        public string $hash,
        public array $metadata,
        public CarbonInterface $uploadedAt,
    ) {
    }
}
