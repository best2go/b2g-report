<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use Best2Go\Best2GoReport\Event\AfterInit;
use Best2Go\Best2GoReport\Event\AfterRow;
use Best2Go\Best2GoReport\Event\Begin;
use Best2Go\Best2GoReport\Event\Best2GoEvent;
use Best2Go\Best2GoReport\Event\End;
use Best2Go\Best2GoReport\Event\Row;
use Best2Go\Best2GoReport\Event\Terminate;
use Best2Go\Best2GoReport\Event\BeforeInit;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

trait EventDispatcherTrait
{
    use EventDispatcherTriggerTrait;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @return mixed */
    protected function trigger(string $eventName, $data): Best2GoEvent
    {
        $event = $this->createEvent($eventName, $data);
        $this->getEventDispatcher()->dispatch($event , $eventName);

        /** @noinspection PhpUnreachableStatementInspection */
        return $event;
    }

    /** @param mixed $data */
    private function createEvent(string $eventName, $data): Best2GoEvent
    {
        switch ($eventName) {
            case Best2GoEvent::EVENT_BEFORE_INIT:
                return new BeforeInit($this, $this->getReportDataProvider(), $this->getContext(), $data);
            case Best2GoEvent::EVENT_AFTER_INIT:
                return new AfterInit($this, $this->getReportDataProvider(), $this->getContext(), $data);
            case Best2GoEvent::EVENT_BEGIN:
                return new Begin($this, $this->getReportDataProvider(), $this->getContext(), $data);
            case Best2GoEvent::EVENT_ROW:
                return new Row($this, $this->getReportDataProvider(), $this->getContext(), $data);
            case Best2GoEvent::EVENT_AFTER_ROW:
                return new AfterRow($this, $this->getReportDataProvider(), $this->getContext(), $data);
            case Best2GoEvent::EVENT_END:
                return new End($this, $this->getReportDataProvider(), $this->getContext(), $data);
            case Best2GoEvent::EVENT_TERMINATE:
                return new Terminate($this, $this->getReportDataProvider(), $this->getContext(), $data);
            default: // unreachable code (!)
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException("can't initiate '" . $eventName . "' event");
                // @codeCoverageIgnoreEnd
        }
    }

    private function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addListener($eventName, $listener, $priority = 0): void
    {
        $this->getEventDispatcher()->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->getEventDispatcher()->addSubscriber($subscriber);
    }

    public function removeListener($eventName, $listener): void
    {
        $this->getEventDispatcher()->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->getEventDispatcher()->removeSubscriber($subscriber);
    }

    public function getListeners($eventName = null): array
    {
        return $this->getEventDispatcher()->getListeners($eventName);
    }

    public function getListenerPriority($eventName, $listener): ?int
    {
        return $this->getEventDispatcher()->getListenerPriority($eventName, $listener);
    }

    public function hasListeners($eventName = null): bool
    {
        return $this->getEventDispatcher()->hasListeners($eventName);
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }
}
