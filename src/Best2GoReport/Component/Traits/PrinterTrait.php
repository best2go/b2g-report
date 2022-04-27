<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use Best2Go\Best2GoReport\Interfaces\PrinterInterface;
use Generator;

trait PrinterTrait
{
    /** @var PrinterInterface */
    private $printer;

    public function getPrinter(): PrinterInterface
    {
        return $this->printer;
    }

    public function setPrinter(PrinterInterface $printer): void
    {
        $this->printer = $printer;
    }

    protected function println($row): iterable
    {
        $this->getPrinter()->println($row);

        return $this->getPrinter()->flush();
    }
}
