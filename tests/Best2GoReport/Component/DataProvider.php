<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use ArrayIterator;
use ArrayObject;
use Best2Go\Best2GoReport\Component\Column;
use Best2Go\Best2GoReport\Event\Best2GoEvent;
use Best2Go\Best2GoReport\Interfaces\ColumnsProviderInterface;
use Best2Go\Best2GoReport\Interfaces\ContextInterface;
use Best2Go\Best2GoReport\Interfaces\EventDispatcherProviderInterface;
use Best2Go\Best2GoReport\Interfaces\HttpHeadersProvider;
use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;
use Best2Go\Best2GoReport\Interfaces\ReportEngineInterface;
use Iterator;
use IteratorAggregate;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class DataProvider
{
    public static function reportDataProvider(): iterable
    {
        $data = [
            [1, 2, 3, 4],
            [4, 3, 2, 1],
            [4, 1, 3, 2],
        ];

        yield 'Iterator' => [
            array_values($data),
            new class ($data) extends ArrayIterator implements Iterator, ReportDataProviderInterface {
                public function getCount(): int
                {
                    return $this->count();
                }
            },
        ];

        yield 'IteratorAggregate' => [
            array_values($data),
            new class ($data) extends ArrayObject implements IteratorAggregate, ReportDataProviderInterface {
                public function getCount(): int
                {
                    return $this->count();
                }
            },
        ];
    }

    public static function reportDataProviderComplete(): iterable
    {
        $date = date('Y-m-d H:i:s');

        yield 'Complete' => [
            [
                'titles' => ['#', '#0', '#1', '#2', '#3', '#4', '#5'], // columns title, check {@see self::getColumns()}
                // [1, 2, 3, 4], // skipped by Base2GoEvent::ROW
                0 => [  1,   2,  3 * 2,  PHP_VERSION,  'first,0', 1 * 2 * 3 * 4, $date],
                1 => [  2,   3,  4 * 2,  PHP_VERSION,  '1'      , 1 * 2 * 3 * 4, $date],
                2 => [  3,   4,  1 * 2,  PHP_VERSION,  '2,last' , 1 * 2 * 3 * 4, $date], // (# last - skipped first row #)
            ],
            new class($date) extends ArrayObject implements
                IteratorAggregate,
                ReportDataProviderInterface,
                ColumnsProviderInterface,
                EventDispatcherProviderInterface,
                EventSubscriberInterface,
                ExpressionFunctionProviderInterface,
                HttpHeadersProvider
            {
                private $date;

                public function __construct(string $date)
                {
                    $this->date = $date;

                    parent::__construct([
                        [1, 2, 3, 4], // <-- skipped by Base2GoEvent::ROW
                        [2, 3, 4, 1],
                        [3, 4, 1, 2],
                        [4, 1, 2, 3],
                    ]);
                }

                public function getEventDispatcher(): EventDispatcherInterface
                {
                    return new EventDispatcher();
                }

                public function getHttpHeaders(): array
                {
                    return ['X-Best2Go' => 'Best2GoReport'];
                }

                public function getColumns(ReportEngineInterface $report): iterable
                {
                    return [
                        new Column('ctx.loop.index',         '#' ), // The current iteration of the loop. (1 indexed)
                        new Column('0',                      '#0'), // array context / numeric string
                        new Column('row[1] * 2',             '#1'), // Expression (x * 2)
                        new Column('const("PHP_VERSION")',   '#2'), // custom expression function
                        new Column([$this, 'callback'],      '#3'), // callable expression
                        new Column(new Expression('p(row)'), '#4'), // core Expression column
                        new Column('ctx.date',               '#5'), // set in EventListener
                    ];
                }

                public function callback($row, ReportDataProviderInterface $provider, ContextInterface $ctx): string
                {
                    // PHPUnit 9.5
                    // assertSame($this, $provider);
                    assert($this === $provider);

                    $result = [];

                    if ($ctx->loop->first) {
                        $result[] = 'first';
                    }

                    $result[] = $ctx->loop->index0;

                    if ($ctx->loop->last) {
                        $result[] = 'last';
                    }

                    return implode(',', $result);
                }

                public function getFunctions(): array {
                    return [
                        ExpressionFunction::fromPhp('constant', 'const'),
                    ];
                }

                public static function getSubscribedEvents(): array
                {
                    return [
                        Best2GoEvent::EVENT_BEFORE_INIT => 'onEventBeforeInit',
                        Best2GoEvent::EVENT_ROW => 'onEventRow',
                    ];
                }

                public function onEventBeforeInit(Best2GoEvent $event): void
                {
                    assert($this === $event->getDataProvider());
                    $engine = $event->getEngine();

                    $engine->registerExpressionFunction(ExpressionFunction::fromPhp('array_product', 'p'));
                    $engine->addListener(
                        Best2GoEvent::EVENT_AFTER_INIT,
                        function (Best2GoEvent $event): void {
                            $event->getContext()->set('date', $this->date);
                        }
                    );
                }

                public function onEventRow(Best2GoEvent $event): void
                {
                    $event->markAsSkipped();
                    $event->getEngine()->removeListener(Best2GoEvent::EVENT_ROW, [$this, 'onEventRow']);
                }

                public function getCount(): int
                {
                    return $this->count();
                }
            }
        ];
    }
}
