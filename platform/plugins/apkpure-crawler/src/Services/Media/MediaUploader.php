<?php

namespace Wallis\ApkpureCrawler\Services\Media;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\Date;
use Wallis\ApkpureCrawler\DTOs\Media\MediaFileDTO;
use Wallis\ApkpureCrawler\Services\Media\Concerns\GeneratesFilenames;
use Wallis\ApkpureCrawler\Services\Media\Concerns\HandlesUrls;
use Wallis\ApkpureCrawler\Services\Media\Concerns\ValidatesMedia;
use Wallis\ApkpureCrawler\Services\Media\Exceptions\MediaException;

final class MediaUploader
{
    use GeneratesFilenames;
    use HandlesUrls;
    use ValidatesMedia;

    private string $disk;

    private ?string $directory = null;

    private ?string $name = null;

    private ?string $visibility = null;

    private array $metadata = [];

    private array $validationRules = [];

    private string $namingStrategy = 'uuid';

    public function __construct(
        private readonly mixed $file,
        private readonly FilesystemFactory $filesystem,
        private readonly string $type
    ) {
        $this->disk = config('plugins.apkpure-crawler.media.default_disk', 'local');
        $this->visibility = config('plugins.apkpure-crawler.media.default_visibility', 'public');
    }

    public function directory(string $path): self
    {
        $this->directory = trim($path, '/');

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        $this->namingStrategy = 'custom';

        return $this;
    }

    public function disk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function visibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function metadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    public function validate(array $rules): self
    {
        $this->validationRules = array_merge($this->validationRules, $rules);

        return $this;
    }

    public function usePublicDisk(): self
    {
        return $this->disk('public')->visibility('public');
    }

    public function usePrivateDisk(): self
    {
        return $this->disk('local')->visibility('private');
    }

    public function makePublic(): self
    {
        return $this->visibility('public');
    }

    public function makePrivate(): self
    {
        return $this->visibility('private');
    }

    public function generateName(): self
    {
        $this->namingStrategy = 'uuid';

        return $this;
    }

    public function hashName(): self
    {
        $this->namingStrategy = 'hash';

        return $this;
    }

    public function preserveName(): self
    {
        $this->namingStrategy = 'preserve';

        return $this;
    }

    public function validateImage(): self
    {
        return $this->validate(['image', 'max:' . config('plugins.apkpure-crawler.media.validation.max_size', 10240)]);
    }

    public function maxSize(int $kilobytes): self
    {
        return $this->validate(['max:' . $kilobytes]);
    }

    public function save(): MediaFileDTO
    {
        $this->performValidation();

        $content = $this->type === 'download'
            ? $this->downloadFileContent()
            : $this->getUploadedFileContent();

        $filename = $this->generateFilename();

        $path = $this->directory
            ? $this->directory . '/' . $filename
            : $filename;

        $options = [];
        if ($this->visibility) {
            $options['visibility'] = $this->visibility;
        }

        $stored = $this->filesystem->disk($this->disk)->put(
            $path,
            $content,
            $options
        );

        if (! $stored) {
            throw new MediaException('Failed to store file');
        }

        return $this->buildDTO($path);
    }

    private function getUploadedFileContent(): string
    {
        return file_get_contents($this->file->getRealPath());
    }

    private function buildDTO(string $path): MediaFileDTO
    {
        $storage = $this->filesystem->disk($this->disk);

        return new MediaFileDTO(
            path: $path,
            fullPath: $storage->path($path),
            url: $storage->url($path),
            disk: $this->disk,
            size: $storage->size($path),
            mimeType: $storage->mimeType($path),
            extension: pathinfo($path, PATHINFO_EXTENSION),
            hash: hash_file('sha256', $storage->path($path)),
            metadata: $this->metadata,
            uploadedAt: Date::now(),
        );
    }
}
