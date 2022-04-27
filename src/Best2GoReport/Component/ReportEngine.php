<?php /** @noinspection PhpSuperClassIncompatibleWithInterfaceInspection */
declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Component\Traits\ColumnsTrait;
use Best2Go\Best2GoReport\Component\Traits\ContextTrait;
use Best2Go\Best2GoReport\Component\Traits\ExpressionLanguageTrait;
use Best2Go\Best2GoReport\Component\Traits\PrinterTrait;
use Best2Go\Best2GoReport\Event\AfterRow;
use Best2Go\Best2GoReport\Event\Best2GoEvent;
use Best2Go\Best2GoReport\Interfaces\ColumnsProviderInterface;
use Best2Go\Best2GoReport\Interfaces\ContextProviderInterface;
use Best2Go\Best2GoReport\Interfaces\EventDispatcherProviderInterface;
use Best2Go\Best2GoReport\Interfaces\HttpHeadersProvider;
use Best2Go\Best2GoReport\Interfaces\PrinterProviderInterface;
use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;
use Best2Go\Best2GoReport\Interfaces\ReportEngineInterface;
use Best2Go\Best2GoReport\Component\Traits\EventDispatcherTrait;
use Best2Go\Best2GoReport\Component\Traits\HttpFoundationTrait;
use Best2Go\Best2GoReport\Component\Traits\ReportDataProviderTrait;
use IteratorAggregate;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Traversable;

class ReportEngine implements IteratorAggregate, ReportEngineInterface, HttpHeadersProvider, PrinterProviderInterface
{
    use ColumnsTrait;
    use ContextTrait;
    use EventDispatcherTrait;
    use ExpressionLanguageTrait;
    use HttpFoundationTrait;
    use PrinterTrait;
    use ReportDataProviderTrait;

    public function __construct(ReportDataProviderInterface $report)
    {
        $this->report = $report;
        $this->columns = new Columns([]);
        $this->engine = new ExpressionLanguage();

        $this->setContext($report instanceof ContextProviderInterface ? $report->getContext() : new NullContext());
        $this->setPrinter($report instanceof PrinterProviderInterface ? $report->getPrinter() : new NullPrinter());

        $this->setEventDispatcher(
            $report instanceof EventDispatcherProviderInterface
                ? $report->getEventDispatcher()
                : new EventDispatcher()
        );

        if ($report instanceof ExpressionFunctionProviderInterface) {
            $this->registerExpressionFunctionProvider($report);
        }

        if ($report instanceof EventSubscriberInterface) {
            $this->addSubscriber($report);
        }

        if ($report instanceof ColumnsProviderInterface) {
            $this->registerColumnsProvider($report);
        }

        $listener = function (AfterRow $event): void {
            $event->getContext()->loopAdvance();
        };

        $this->getEventDispatcher()->addListener(Best2GoEvent::EVENT_BEFORE_INIT, function (): void {
            $this->ensureColumns();
        });

        $this->getEventDispatcher()->addListener(Best2GoEvent::EVENT_BEGIN, function () use ($listener): void {
            $this->getEventDispatcher()->addListener(Best2GoEvent::EVENT_AFTER_ROW, $listener);
        });

        $this->getEventDispatcher()->addListener(Best2GoEvent::EVENT_END, function () use ($listener): void {
            $this->getEventDispatcher()->removeListener(Best2GoEvent::EVENT_AFTER_ROW, $listener);
        });
    }

    final public function getIterator(): Traversable
    {
        $this->init();
        $this->trigger(Best2GoEvent::EVENT_BEGIN, null);

        foreach ($this->getReportDataProvider() as $row) {
            $event = $this->trigger(Best2GoEvent::EVENT_ROW, $row);

            if ($event->isSkipped()) {
                // TODO: loopAdvance()?! no, out-of-scope (decision maker - skipper owner)
                continue;
            }

            $row = $event->getData();
            $row = $this->dumpRow($row);
            yield from $this->println($row);

            $this->trigger(Best2GoEvent::EVENT_AFTER_ROW, $row);
        }

        yield from $this->getPrinter()->flush();
        $this->trigger(Best2GoEvent::EVENT_END, null);
        yield from $this->getPrinter()->flush();

        $this->finish();
    }

    protected function init(): void
    {
        $this->trigger(Best2GoEvent::EVENT_BEFORE_INIT, null);

        $this->initColumns();
        $this->initContext();
        $this->initPrinter();

        $this->trigger(Best2GoEvent::EVENT_AFTER_INIT, null);
    }

    protected function finish(): void
    {
        $this->trigger(Best2GoEvent::EVENT_TERMINATE, null);
    }

    public function getTitles(): array
    {
        return $this->getColumns()->getTitles();
    }

    protected function initContext(): void
    {
        $this->getContext()->init($this);
    }

    protected function initPrinter(): void
    {
        $this->getPrinter()->init($this);
    }

    protected function dumpRow($row): array
    {
        $data = [];
        foreach ($this->getColumns() as $column) {
            $data[] = $this->dumpColumn($column, $row);
        }

        return $data;
    }

    protected function dumpColumn(Column $column, $row)
    {
        $expression = $column->getExpression();

        if (is_callable($expression)) {
            return call_user_func_array($expression, [$row, $this->getReportDataProvider(), $this->context]);
        }

        if ($expression instanceof Expression) {
            return $this->engine->evaluate(
                $expression,
                ['row' => $row, 'collection' => $this->getReportDataProvider(), 'ctx' => $this->context]
            );
        }

        // unreachable code
        // @codeCoverageIgnoreStart
        throw new RuntimeException("can't eval '" . $column->getTitle() . "'");
        // @codeCoverageIgnoreEnd
    }
}
