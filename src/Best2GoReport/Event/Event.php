<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Contracts\EventDispatcher\Event as ContractEvent;

/** Base class for events, BC compatible. */
if (class_exists(Kernel::class) && Kernel::VERSION_ID >= 40300 && class_exists(ContractEvent::class)) {
    class Event extends ContractEvent
    {
    }
} else if (!class_exists(Kernel::class) && class_exists(ContractEvent::class)) {
    class Event extends ContractEvent
    {
    }
} else {
    class Event extends BaseEvent
    {
    }
}
