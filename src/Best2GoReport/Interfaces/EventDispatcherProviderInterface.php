<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface EventDispatcherProviderInterface
{
    public function getEventDispatcher(): EventDispatcherInterface;
}
