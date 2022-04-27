<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use RuntimeException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractEventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcher;

/** Base class for events, BC compatible. */
if (interface_exists(ContractEventDispatcher::class) && interface_exists(PsrEventDispatcher::class, true)) {
    /** @codeCoverageIgnore */
    trait EventDispatcherTriggerTrait
    {
        public function dispatch(object $event, string $eventName = null): object
        {
            throw new RuntimeException('avoid public dispatch()');
        }
    }
} else {
    /** @codeCoverageIgnore */
    trait EventDispatcherTriggerTrait
    {
        public function dispatch($event/* , string $eventName = null */)
        {
            throw new RuntimeException('avoid public dispatch()');
        }
    }
}
