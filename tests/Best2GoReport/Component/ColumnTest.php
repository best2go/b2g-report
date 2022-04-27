<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Component\Column;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    public function testColumn(): void
    {
        $column = new Column(1, 'title', 'description');
        $this->assertSame(1, $column->getExpression());
        $this->assertSame('title', $column->getTitle());
        $this->assertSame('description', $column->getDescription());

        $column = new Column('2', 'title');
        $this->assertSame('2', $column->getExpression());
        $this->assertSame('title', $column->getTitle());
        $this->assertSame('title', $column->getDescription());

        $column = new Column('expr');
        $this->assertSame('expr', $column->getExpression(), 'expression');
        $this->assertSame('expr', $column->getTitle(), 'title');
        $this->assertSame('expr', $column->getDescription(), 'description');
    }
}
