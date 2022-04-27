<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use Best2Go\Best2GoReport\HttpFoundation\CsvResponse;
use Best2Go\Best2GoReport\HttpFoundation\TextTableResponse;
use Best2Go\Best2GoReport\Interfaces\HttpHeadersProvider;
use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;
use Symfony\Component\HttpFoundation\Response;

trait HttpFoundationTrait
{
    abstract protected function getReportDataProvider(): ReportDataProviderInterface;

    /** @return CsvResponse */
    public function getCsvResponse(int $status = 200, array $headers = []): Response
    {
        return new CsvResponse($this, $status, $headers);
    }

    public function getTextTableResponse(int $status = 200, array $headers = []): Response
    {
        return new TextTableResponse($this, $status, $headers);
    }

    public function getHttpHeaders(): array
    {
        return $this->getReportDataProvider() instanceof HttpHeadersProvider
            ? $this->getReportDataProvider()->getHttpHeaders()
            : [];
    }
}
