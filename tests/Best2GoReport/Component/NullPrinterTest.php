<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Component\NullPrinter;
use Generator;
use PHPUnit\Framework\TestCase;

class NullPrinterTest extends TestCase
{
    public function testPrinter(): void
    {
        $data = [
            'Hello World!',
            'foo/bar',
        ];

        $printer = new NullPrinter();
        foreach ($data as $row) {
            $printer->println($row);
        }

        $output = $printer->flush();
        $this->assertInstanceOf(Generator::class, $output);
        $this->assertSame($data, iterator_to_array($output));
    }

    public function testAppendPrinter(): void
    {
        $data = [
            'l1' => 'Hello World!',
            'l2' => '#GloryToUkraine',
            'l3' => '#StandWithUkraine',
        ];

        $printer = new NullPrinter();
        $printer->append($data['l3']);
        $printer->println($data['l1']);
        $printer->println($data['l2']);

        $output = $printer->flush();

        $this->assertSame(array_values($data), iterator_to_array($output));
    }
}
