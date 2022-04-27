<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Event\Best2GoEvent;
use Best2Go\Best2GoReport\Interfaces\ReportEngineInterface;
use RuntimeException;

/**
 * @property-read bool $first
 * @property-read bool $last
 * @property-read int $index0
 * @property-read int $index
 * @property-read int $revindex
 * @property-read int $revindex1
 * @property-read int $length
 */
class CtxLoop
{
    private $loop0;
    private $length;
    private $pos; // real pos
    private $first = true;

    public function __construct(ReportEngineInterface $report)
    {
        $this->length = $report->getCount();
        $this->loop0 = 0;
        $this->pos = 0;

        $report->addListener(
            Best2GoEvent::EVENT_AFTER_ROW,
            function (): void {
                $this->first = false;
            },
            100500
        );

        $report->addListener(
            Best2GoEvent::EVENT_ROW,
            function (): void {
                $this->pos ++;
            },
            100500
        );
    }

    public function __get($name)
    {
        switch ($name) {
            case 'first':
                return $this->first;
            case 'last':
                return $this->pos === $this->length;
            case 'index0':
                return $this->loop0;
            case 'index':
                return $this->loop0 + 1;
            case 'revindex':
                return $this->length - $this->loop0 - 1;
            case 'revindex1':
                return $this->length - $this->loop0;
            case 'length':
                return $this->length;
            default:
                throw new RuntimeException("can't eval loop.<" . $name . ">");
        }
    }

    public function advance(): void
    {
        $this->loop0 += 1;
    }
}
