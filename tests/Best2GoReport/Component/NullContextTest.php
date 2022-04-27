<?php declare(strict_types=1);

namespace Tests\Best2Go\Best2GoReport\Component;

use Best2Go\Best2GoReport\Component\NullContext;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class NullContextTest extends TestCase
{
    public function testNullContext(): void
    {
        $ctx = new NullContext();
        $ctx->set('foo', 'bar');

        /** @noinspection PhpUndefinedFieldInspection */
        self::assertSame('bar', $ctx->foo);
    }

    public function testOutboundVariable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("can't eval ctx.<outbound>");

        $ctx = new NullContext();
        $ctx->set('foo', 'bar');

        /** @noinspection PhpUndefinedFieldInspection */
        self::assertSame('bar', $ctx->foo);

        /** @noinspection PhpExpressionResultUnusedInspection */
        $ctx->outbound === true;
    }
}
