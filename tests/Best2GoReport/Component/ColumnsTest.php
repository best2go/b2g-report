<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Component\Column;
use Best2Go\Best2GoReport\Component\Columns;
use PHPUnit\Framework\TestCase;

class ColumnsTest extends TestCase
{
    public function testColumns(): void
    {
        $columns = new Columns();
        $columns->addColumn($c0 = new Column(0, '#0'));
        $columns->addColumn($c1 = new Column(0, '#1'));
        $columns->setColumn('A', $c2 = new Column(0, '#2'));

        $this->assertSame(['#0', '#1', 'A' => '#2'], $columns->getTitles());
        $this->assertTrue($c2 === $columns->getColumn('A'));
        $this->assertSame([$c0, $c1, 'A' => $c2], iterator_to_array($columns));
    }
}
