<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

interface PrinterProviderInterface
{
    public function getPrinter(): PrinterInterface;
}
