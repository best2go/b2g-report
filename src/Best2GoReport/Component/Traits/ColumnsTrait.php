<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use Best2Go\Best2GoReport\Component\Column;
use Best2Go\Best2GoReport\Component\Columns;
use Best2Go\Best2GoReport\Interfaces\ColumnsProviderInterface;
use Best2Go\Best2GoReport\Interfaces\PrinterInterface;
use Best2Go\Best2GoReport\Interfaces\PrinterProviderInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

trait ColumnsTrait
{
    /** @var Columns */
    private $columns;

    public function addColumn(Column $column): void
    {
        $this->getColumns()->addColumn($column);
    }

    public function registerColumnsProvider(ColumnsProviderInterface $provider): void
    {
        foreach ($provider->getColumns($this) as $column) {
            $this->addColumn($column);
        }
    }

    protected function ensureColumns(): void
    {
        if ($this->getColumns()->getTitles()) {
            return;
        }

        $data = array_values(iterator_to_array($this->report));

        if (empty($data)) {
            return;
        }

        foreach (array_keys($data[0]) as $key) {
            $this->addColumn(new Column('row[' . $key . ']', (string) $key));
        }
    }

    protected function initColumns(): void
    {
        foreach ($this->getColumns() as $pos => $column) {
            $expression = $column->getExpression();

            switch (true) {
                case is_callable($expression):
                    break;
                case is_numeric($expression):
                    $this->resetColumnExpression($pos, $this->parse('row[' . $expression . ']'));
                    break;
                default:
                case is_string($expression):
                case $expression instanceof Expression:
                    $this->resetColumnExpression($pos, $this->parse($expression));
                    break;
            }
        }
    }

    protected function getColumns(): Columns
    {
        return $this->columns;
    }

    /** @param mixed $pos */
    protected function setColumn($pos, Column $column): void
    {
        $this->getColumns()->setColumn($pos, $column);
    }

    /** @param mixed $pos */
    private function getColumnByPos($pos): Column
    {
        return $this->getColumns()->getColumn($pos);
    }

    /**
     * @param mixed $pos
     * @param Expression|mixed $expression
     */
    private function resetColumnExpression($pos, $expression): void
    {
        $column = $this->getColumnByPos($pos);
        $this->setColumn($pos, new Column($expression, $column->getTitle(), $column->getDescription()));
    }
}
