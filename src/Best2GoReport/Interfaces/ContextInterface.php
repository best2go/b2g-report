<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

interface ContextInterface
{
    public function init(ReportEngineInterface $report): void;
    public function set(string $key, $value): void;
    public function loopAdvance(): void;
}
