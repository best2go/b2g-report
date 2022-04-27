<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

use IteratorAggregate;
use Traversable;

interface ReportDataProviderInterface extends Traversable
{
    public function getCount(): int;
}
