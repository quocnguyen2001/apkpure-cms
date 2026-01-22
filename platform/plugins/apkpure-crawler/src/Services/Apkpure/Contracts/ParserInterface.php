<?php

namespace Wallis\ApkpureCrawler\Services\Apkpure\Contracts;

interface ParserInterface
{
    public function parse(string $html): object|array;
}
