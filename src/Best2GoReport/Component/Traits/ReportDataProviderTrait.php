<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;

trait ReportDataProviderTrait
{
    /** @var ReportDataProviderInterface */
    private $report;

    protected function getReportDataProvider(): ReportDataProviderInterface
    {
        return $this->report;
    }

    public function getCount(): int
    {
        return $this->getReportDataProvider()->getCount();
    }
}
