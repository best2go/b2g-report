<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\HttpFoundation;

use Best2Go\Best2GoReport\Event\AfterInit;

class CsvResponse extends BinaryStreamedResponse
{

    protected function getHttpHeaders(): array
    {
        return array_merge(
            parent::getHttpHeaders(),
            ['Content-Type' => 'text/csv']
        );
    }

    protected function renderHeader(AfterInit $event): void
    {
        fputcsv($this->getStream(), $this->report->getTitles());
    }

    /** @param array|mixed $row */
    protected function renderRow($row): void
    {
        fputcsv($this->getStream(), $row);
    }
}
