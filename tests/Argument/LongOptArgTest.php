<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

use Generator;
use Ordinary\Command\UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Throwable;

class LongOptArgTest extends TestCase
{
    public static function fromArgProvider(): Generator
    {
        yield ['', UnexpectedValueException::class];
        yield ['--', UnexpectedValueException::class];
        yield ['-f', UnexpectedValueException::class];
        yield ['-foo', UnexpectedValueException::class];
        yield ['--foo', null];
        yield ['--f', null];
    }

    public static function objectFromArgProvider(): Generator
    {
        yield ['--foo', 'foo', false, null];
        yield ['--foo=bar', 'foo', true, 'bar'];
        yield ['--foo=', 'foo', true, ''];
    }

    /**
     * @param ?class-string<Throwable> $exception
     * @dataProvider fromArgProvider
     */
    public function testFromArg(string $arg, ?string $exception): void
    {
        if ($exception) {
            self::expectException($exception);
        }

        $result = LongOptArg::fromArg($arg);

        self::assertInstanceOf(LongOptArg::class, $result);
    }

    /** @dataProvider objectFromArgProvider */
    public function testObjectFromArg(string $arg, string $name, bool $hasValue, ?string $value): void
    {
        $obj = LongOptArg::fromArg($arg);

        self::assertSame($name, $obj->name());
        self::assertSame($hasValue, $obj->hasValue());
        self::assertSame($value, $obj->value());
    }
}
