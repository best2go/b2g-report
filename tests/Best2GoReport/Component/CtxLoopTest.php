<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use ArrayIterator;
use ArrayObject;
use Best2Go\Best2GoReport\Component\Column;
use Best2Go\Best2GoReport\Component\CtxLoop;
use Best2Go\Best2GoReport\Component\ReportEngine;
use Best2Go\Best2GoReport\Event\Best2GoEvent;
use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;
use Iterator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CtxLoopTest extends TestCase
{
    public function testCtxLoop(): void
    {
        $array = array_fill(0, 5, null);
        $engine = new ReportEngine(
            new class($array) extends ArrayIterator implements Iterator, ReportDataProviderInterface
            {
                public function getCount(): int
                {
                    return $this->count();
                }
            }
        );

        $engine->addColumn(new Column('ctx.loop.first'));
        $engine->addColumn(new Column('ctx.loop.last'));
        $engine->addColumn(new Column('ctx.loop.index'));
        $engine->addColumn(new Column('ctx.loop.index0'));
        $engine->addColumn(new Column('ctx.loop.revindex'));
        $engine->addColumn(new Column('ctx.loop.revindex1'));
        $engine->addColumn(new Column('ctx.loop.length'));
        $engine->addColumn(new Column('row[0]'));

        $engine->addListener(Best2GoEvent::EVENT_ROW, static function (Best2GoEvent $event) {
            $event->setData(['abc' . $event->getContext()->loop->index0]);
        });

        $expected = [
            [true , false, 1, 0, 4, 5, 5, 'abc0'],
            [false, false, 2, 1, 3, 4, 5, 'abc1'],
            [false, false, 3, 2, 2, 3, 5, 'abc2'],
            [false, false, 4, 3, 1, 2, 5, 'abc3'],
            [false,  true, 5, 4, 0, 1, 5, 'abc4'],
        ];

        $actual = iterator_to_array($engine, false);
        self::assertSame($expected, $actual);
    }

    /** @dataProvider \Tests\Best2Go\Best2GoReport\Component\DataProvider::reportDataProvider() */
    public function testOutboundVariable(array $data, ReportDataProviderInterface $provider): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("can't eval loop.<outbound>");

        $ctxLoop = new CtxLoop(new ReportEngine($provider));
        /** @noinspection PhpExpressionResultUnusedInspection */
        $ctxLoop->outbound === true;
    }
}
