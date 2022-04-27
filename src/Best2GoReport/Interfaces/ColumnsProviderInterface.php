<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

use Best2Go\Best2GoReport\Component\Column;

interface ColumnsProviderInterface
{
    /** @return Column[] */
    public function getColumns(ReportEngineInterface $report): iterable;
}
