<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Interfaces\ReportDataProviderInterface;
use PHPUnit\Framework\TestCase;

class ReportDataProviderTest extends TestCase
{
    /** @dataProvider \Tests\Best2Go\Best2GoReport\Component\DataProvider::reportDataProvider() */
    public function testReportDataProvider(array $data, ReportDataProviderInterface $provider): void
    {
        $this->assertSame($data, iterator_to_array($provider));
        $this->assertSame(count($data), $provider->getCount());
    }
}
