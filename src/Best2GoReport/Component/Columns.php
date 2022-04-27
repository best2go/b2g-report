<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component;

use Generator;
use IteratorAggregate;
use Traversable;

class Columns implements IteratorAggregate
{
    /** @var Column[] */
    private $columns;

    /** @param Column[] $columns */
    public function __construct(array $columns = [])
    {
        $this->columns = $columns;
    }

    public function addColumn(Column $column): void
    {
        $this->columns[] = $column;
    }

    /** @param mixed $pos */
    public function setColumn($pos, Column $column): void
    {
        $this->columns[$pos] = $column;
    }

    /** @param mixed $pos */
    public function getColumn($pos): Column
    {
        return $this->columns[$pos];
    }

    /** @return string[] */
    public function getTitles(): array
    {
        $titles = [];
        foreach ($this->columns as $pos => $column) {
            $titles[$pos] = $column->getTitle();
        }

        return $titles;
    }

    /** @return Generator<array-key, Column> */
    public function getIterator(): Traversable
    {
        foreach ($this->columns as $pos => $column) {
            yield $pos => $column;
        }
    }
}
