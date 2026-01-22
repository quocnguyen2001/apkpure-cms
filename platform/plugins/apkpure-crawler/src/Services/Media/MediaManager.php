<?php

namespace Wallis\ApkpureCrawler\Services\Media;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

final readonly class MediaManager
{
    public function __construct(
        private FilesystemFactory $filesystem
    ) {
    }

    public function upload(mixed $file): MediaUploader
    {
        return new MediaUploader(
            file: $file,
            filesystem: $this->filesystem,
            type: 'upload'
        );
    }

    public function downloadFromUrl(string $url): MediaUploader
    {
        return new MediaUploader(
            file: $url,
            filesystem: $this->filesystem,
            type: 'download'
        );
    }
}
