<?php

namespace Wallis\ApkpureCrawler\Services\Media\Concerns;

use Illuminate\Support\Str;

trait GeneratesFilenames
{
    private function generateFilename(): string
    {
        $extension = $this->getFileExtension();

        return match ($this->namingStrategy) {
            'custom' => $this->name . '.' . $extension,
            'hash' => $this->generateHashName($extension),
            'preserve' => $this->getOriginalName(),
            default => Str::uuid() . '.' . $extension,
        };
    }

    private function generateHashName(string $extension): string
    {
        $content = $this->type === 'download'
            ? $this->downloadFileContent()
            : file_get_contents($this->file->getRealPath());

        return hash('sha256', $content) . '.' . $extension;
    }

    private function getOriginalName(): string
    {
        if ($this->type === 'download') {
            return basename(parse_url($this->file, PHP_URL_PATH));
        }

        return $this->file->getClientOriginalName();
    }

    private function getFileExtension(): string
    {
        if ($this->type === 'download') {
            $path = parse_url($this->file, PHP_URL_PATH);

            return pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';
        }

        return $this->file->getClientOriginalExtension();
    }
}
