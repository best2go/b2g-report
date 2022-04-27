<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\HttpFoundation;

use Best2Go\Best2GoReport\Event\AfterInit;
use Best2Go\Best2GoReport\Event\Best2GoEvent;
use Best2Go\Best2GoReport\Event\End;
use Best2Go\Best2GoReport\Event\Terminate;
use Best2Go\Best2GoReport\Interfaces\HttpHeadersProvider;
use Best2Go\Best2GoReport\Interfaces\ReportEngineInterface;
use Best2Go\Best2GoReport\Event\BeforeInit;
use Symfony\Component\HttpFoundation\StreamedResponse as HttpStreamedResponse;

abstract class StreamedResponse extends HttpStreamedResponse
{
    /** @var ReportEngineInterface */
    protected $report;

    final public function __construct(ReportEngineInterface $report, int $status = 200, array $headers = [])
    {
        $this->report = $report;
        $headers = array_merge(
            $this->getHttpHeaders(),
            $report instanceof HttpHeadersProvider ? $report->getHttpHeaders() : [],
            $headers
        );

        $report->addListener(Best2GoEvent::EVENT_BEFORE_INIT, [$this, 'onReportBeforeInit']);
        $report->addListener(Best2GoEvent::EVENT_AFTER_INIT, [$this, 'onReportAfterInit']);
        $report->addListener(Best2GoEvent::EVENT_END, [$this, 'onReportEnd']);
        $report->addListener(Best2GoEvent::EVENT_TERMINATE, [$this, 'onReportTerminate']);

        parent::__construct([$this, 'render'], $status, $headers);
    }

    final public function onReportBeforeInit(BeforeInit $event): void
    {
        $this->init($event);
    }

    final public function onReportAfterInit(AfterInit $event): void
    {
        $this->renderHeader($event);
    }

    final public function onReportEnd(End $event): void
    {
        $this->renderFooter($event);
    }

    final public function onReportTerminate(Terminate $event): void
    {
        $this->finish($event);
    }

    protected function render(): void
    {
        foreach ($this->report as $row) {
            $this->renderRow($row);
        }
    }

    protected function init(BeforeInit $event): void
    {
        // noop!
    }

    protected function finish(Terminate $event): void
    {
        // noop!
    }

    protected function renderHeader(AfterInit $event): void
    {
        // what a default behaviour ??!
    }

    /** @param mixed $row */
    protected function renderRow($row): void
    {
        // what a default behaviour ??!
    }

    protected function renderFooter(End $event): void
    {
        // what a default behaviour ??!
    }

    protected function getHttpHeaders(): array
    {
        return [];
    }
}
