<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Interfaces\PrinterInterface;
use Best2Go\Best2GoReport\Interfaces\ReportEngineInterface;
use Generator;

class NullPrinter implements PrinterInterface
{
    private $buffer = [];
    private $post = [];

    public function init(ReportEngineInterface $engine)
    {
        // noop!
    }

    public function flush(): iterable
    {
        $buffer = array_merge($this->buffer, $this->post);
        $this->buffer = [];
        $this->post = [];

        yield from $buffer;
    }

    public function println($row): void
    {
        $this->buffer[] = $row;
    }

    public function append($row): void
    {
        $this->post[] = $row;
    }
}
