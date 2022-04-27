<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Event;

use Best2Go\Best2GoReport\Interfaces\ContextInterface;
use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;
use Best2Go\Best2GoReport\Interfaces\ReportEngineInterface;

abstract class Best2GoEvent extends Event
{
    public const EVENT_BEFORE_INIT = 'best2go.report.before_init';
    public const EVENT_AFTER_INIT = 'best2go.report.after_init';
    public const EVENT_BEGIN = 'best2go.report.begin';
    public const EVENT_ROW = 'best2go.report.row';
    public const EVENT_AFTER_ROW = 'best2go.report.after_row';
    public const EVENT_END = 'best2go.report.end';
    public const EVENT_TERMINATE = 'best2go.report.terminate';

    /** @var ReportEngineInterface */
    private $engine;

    /** @var ReportDataProviderInterface */
    private $dataProvider;

    /** @var ContextInterface */
    private $context;

    /** @var mixed */
    private $data;

    /** @var bool */
    private $skip = false;

    public function __construct(
        ReportEngineInterface $engine,
        ReportDataProviderInterface $dataProvider,
        ContextInterface $context,
        $data
    ) {
        $this->engine = $engine;
        $this->dataProvider = $dataProvider;
        $this->context = $context;
        $this->data = $data;
    }

    public function getEngine(): ReportEngineInterface
    {
        return $this->engine;
    }

    public function getDataProvider(): ReportDataProviderInterface
    {
        return $this->dataProvider;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    /** @return mixed */
    public function getData()
    {
        return $this->data;
    }

    public function markAsSkipped(): void
    {
        $this->skip = true;
    }

    public function isSkipped(): bool
    {
        return $this->skip;
    }
}
