<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\HttpFoundation;

use Best2Go\Best2GoReport\Event\AfterInit;
use Best2Go\Best2GoReport\Event\BeforeInit;
use Best2Go\Best2GoReport\Event\Terminate;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\StreamOutput;

class TextTableResponse extends BinaryStreamedResponse
{
    /** @var Table */
    private $table;

    protected function getHttpHeaders(): array
    {
        return array_merge(
            parent::getHttpHeaders(),
            ['Content-Type' => 'text/plain']
        );
    }

    protected function init(BeforeInit $event): void
    {
        parent::init($event);
        $this->table = new Table(new StreamOutput($this->getStream()));
    }

    protected function renderHeader(AfterInit $event): void
    {
        $this->table->setHeaders($this->report->getTitles());
    }

    /** @param array|mixed $row */
    protected function renderRow($row): void
    {
        $this->table->addRow($row);
    }

    protected function finish(Terminate $event): void
    {
        if ($this->headers->has('x-transpose')) {
            $this->table->setHorizontal((bool) $this->headers->get('x-transpose'));
        }

        $this->table->render();
        parent::finish($event);
        $this->table = null;
    }
}
