<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

interface HttpHeadersProvider
{
    public function getHttpHeaders(): array;
}
