<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

use Generator;

interface PrinterInterface
{
    public function init(ReportEngineInterface $engine);
    public function println($row): void;
    // public function append($row): void;
    public function flush(): iterable;
}
