<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use Best2Go\Best2GoReport\Interfaces\ContextInterface;
use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;

trait ContextTrait
{
    /** @var ContextInterface */
    private $context;

    abstract protected function getReportDataProvider(): ReportDataProviderInterface;

    protected function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }

    protected function getContext(): ContextInterface
    {
        return $this->context;
    }
}
