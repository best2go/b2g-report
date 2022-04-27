<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\HttpFoundation;

use Best2Go\Best2GoReport\Event\BeforeInit;
use Best2Go\Best2GoReport\Event\Terminate;

class BinaryStreamedResponse extends StreamedResponse
{
    /** @var resource */
    private $stream;

    protected function getStream()
    {
        return $this->stream;
    }

    protected function init(BeforeInit $event): void
    {
        $this->stream = fopen('php://temp', 'wb+');
        parent::init($event);
    }

    protected function finish(Terminate $event): void
    {
        rewind($this->getStream());
        fpassthru($this->getStream());

        parent::finish($event);
    }
}
