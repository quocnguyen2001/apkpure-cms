<?php

namespace Wallis\ApkpureCrawler\Services\Media\Concerns;

use Illuminate\Support\Facades\Validator;
use Wallis\ApkpureCrawler\Services\Media\Exceptions\InvalidFileException;

trait ValidatesMedia
{
    private function performValidation(): void
    {
        if ($this->validationRules === []) {
            return;
        }

        $validator = Validator::make(
            ['file' => $this->file],
            ['file' => $this->validationRules]
        );

        if ($validator->fails()) {
            throw new InvalidFileException(
                $validator->errors()->first('file')
            );
        }
    }
}
