<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Interfaces\ContextInterface;
use Best2Go\Best2GoReport\Interfaces\ReportEngineInterface;
use RuntimeException;

/**
 * @property-read CtxLoop $loop
 */
class NullContext implements ContextInterface
{
    /** @var CtxLoop */
    private $loop;

    /** @var array */
    private $variables = [];

    // timer - (begin / end / avg ??
    // debug info - ? (SQL dumper, so on)

    public function init(ReportEngineInterface $report): void
    {
        $this->loop = new CtxLoop($report);
    }

    public function __get($name)
    {
        switch (true) {
            case $name === 'loop':
                return $this->loop;
            case key_exists($name, $this->variables):
                return $this->variables[$name];
            default:
                throw new RuntimeException("can't eval ctx.<" . $name . ">");
        }
    }

    public function set(string $key, $value): void
    {
        $this->variables[$key] = $value;
    }

    public function loopAdvance(): void
    {
        $this->loop->advance();
    }
}
