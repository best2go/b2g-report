<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use ArrayIterator;
use Best2Go\Best2GoReport\Component\ReportEngine;
use Best2Go\Best2GoReport\Event\Best2GoEvent;
use Best2Go\Best2GoReport\Event\Event;
use Best2Go\Best2GoReport\Interfaces\EventDispatcherProviderInterface;
use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportEngineTest extends TestCase
{
    public function testEmptyReportEngine(): void
    {
        $engine = new ReportEngine(
            new class([]) extends ArrayIterator implements Iterator, ReportDataProviderInterface
            {
                public function getCount(): int
                {
                    return $this->count();
                }
            }
        );
        self::assertSame([], iterator_to_array($engine, false));
    }

    public function testEventDispatcher(): void
    {
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();

        $engine = new ReportEngine(
            new class([], $eventDispatcher) extends ArrayIterator implements
                Iterator,
                ReportDataProviderInterface,
                EventDispatcherProviderInterface
            {
                private $eventDispatcher;

                public function __construct(array $data, EventDispatcher $eventDispatcher)
                {
                    parent::__construct($data);
                    $this->eventDispatcher = $eventDispatcher;
                }

                public function getEventDispatcher(): EventDispatcherInterface
                {
                    return $this->eventDispatcher;
                }

                public function getCount(): int
                {
                    return $this->count();
                }
            }
        );

        $eventDispatcher->expects(self::atLeastOnce())->method('addListener');
        $eventDispatcher->expects(self::atLeastOnce())->method('addSubscriber');
        $eventDispatcher->expects(self::atLeastOnce())->method('removeListener');
        $eventDispatcher->expects(self::atLeastOnce())->method('removeSubscriber');
        $eventDispatcher->expects(self::atLeastOnce())->method('hasListeners')->willReturn(false);
        $eventDispatcher->expects(self::atLeastOnce())->method('getListeners')->willReturn([]);
        $eventDispatcher->expects(self::atLeastOnce())->method('getListenerPriority');

        $engine->addListener(Best2GoEvent::EVENT_BEFORE_INIT, []);
        $engine->getListeners(Best2GoEvent::EVENT_BEFORE_INIT);
        $engine->hasListeners();
        $engine->getListenerPriority(Best2GoEvent::EVENT_BEFORE_INIT, []);
        $engine->removeListener(Best2GoEvent::EVENT_BEFORE_INIT, []);
        $engine->addSubscriber($this->getMockForAbstractClass(EventSubscriberInterface::class));
        $engine->removeSubscriber($this->getMockForAbstractClass(EventSubscriberInterface::class));

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('avoid public dispatch()');
        $engine->dispatch(new Event());
    }

    /** @dataProvider \Tests\Best2Go\Best2GoReport\Component\DataProvider::reportDataProvider() */
    public function testReportEngineIterators(array $data, ReportDataProviderInterface $provider): void
    {
        $engine = new ReportEngine($provider);
        $pos = 0;
        foreach ($engine as $row) {
            self::assertSame($data[$pos++], $row);
        }
    }

    /** @dataProvider \Tests\Best2Go\Best2GoReport\Component\DataProvider::reportDataProviderComplete() */
    public function testReportEngine(array $data, ReportDataProviderInterface $provider): void
    {
        $engine = new ReportEngine($provider);
        $pos = 0;
        foreach ($engine as $row) {
            self::assertSame($data[$pos++], $row);
        }
        self::assertSame($data['titles'], $engine->getTitles());
    }

    /** @dataProvider \Tests\Best2Go\Best2GoReport\Component\DataProvider::reportDataProviderComplete() */
    public function testCsvResponse(array $data, ReportDataProviderInterface $provider): void
    {
        $engine = new ReportEngine($provider);
        $csvResponse = $engine->getCsvResponse();
        self::assertSame(200, $csvResponse->getStatusCode());
        self::assertSame('text/csv', $csvResponse->headers->get('Content-Type'));
        self::assertSame('Best2GoReport', $csvResponse->headers->get('x-best2go'));
        $content = $this->getContent($csvResponse);

        self::assertSame($data['titles'], $engine->getTitles());
        $expected = $this->array2csv($data);
        $this->assertSame($expected, $content);
    }

    /** @dataProvider \Tests\Best2Go\Best2GoReport\Component\DataProvider::reportDataProviderComplete() */
    public function testTextTableResponse(array $data, ReportDataProviderInterface $provider): void
    {
        $engine = new ReportEngine($provider);
        $csvResponse = $engine->getTextTableResponse();
        self::assertSame(200, $csvResponse->getStatusCode());
        self::assertSame('text/plain', $csvResponse->headers->get('Content-Type'));
        self::assertSame('Best2GoReport', $csvResponse->headers->get('x-best2go'));
        $content = $this->getContent($csvResponse);

        self::assertSame($data['titles'], $engine->getTitles());
        $expected = $this->array2table($data, false);
        $this->assertSame($expected, $content);
    }


    /** @dataProvider \Tests\Best2Go\Best2GoReport\Component\DataProvider::reportDataProviderComplete() */
    public function testTextTransposeTableResponse(array $data, ReportDataProviderInterface $provider): void
    {
        $engine = new ReportEngine($provider);
        $csvResponse = $engine->getTextTableResponse(200, ['X-Transpose' => true]);
        self::assertSame(200, $csvResponse->getStatusCode());
        $content = $this->getContent($csvResponse);

        self::assertSame($data['titles'], $engine->getTitles());
        $expected = $this->array2table($data, true);
        $this->assertSame($expected, $content);
    }

    private function array2table(array $data, bool $transpose): string
    {
        $stream = fopen('php://temp', 'wb+');
        $table = new Table(new StreamOutput($stream));
        $table->setHorizontal($transpose);
        $table->setHeaders(array_shift($data));
        $table->addRows($data);
        $table->render();

        return $this->getContent($stream);
    }

    private function getContent($stream): string
    {
        switch (true) {
            case is_resource($stream):
                return $this->getStreamContent($stream);
            case $stream instanceof StreamedResponse:
                return $this->getResponseContent($stream);
            default:
                throw new InvalidArgumentException('please pass resource or StreamedResponse');
        }
    }

    private function getResponseContent(StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }

    private function getStreamContent($stream): string
    {
        rewind($stream);
        ob_start();
        fpassthru($stream);
        $content = ob_get_clean();
        fclose($stream);

        return $content;
    }

    private function array2csv($array): string
    {
        $stream = fopen('php://temp', 'wb+');
        array_map(function ($row) use ($stream) { fputcsv($stream, $row); }, $array);

        return $this->getContent($stream);
    }
}
