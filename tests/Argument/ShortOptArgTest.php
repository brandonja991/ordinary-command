<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

use Generator;
use Ordinary\Command\UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Throwable;

class ShortOptArgTest extends TestCase
{
    public static function fromArgProvider(): Generator
    {
        yield ['', UnexpectedValueException::class];
        yield ['--', UnexpectedValueException::class];
        yield ['-f', null];
        yield ['-foo', null];
        yield ['--foo', UnexpectedValueException::class];
        yield ['--f', UnexpectedValueException::class];
    }

    public static function objectFromArgProvider(): Generator
    {
        yield ['-a', 'a', null];
        yield ['-abc', 'a', 'bc'];
        yield ['-a=bc', 'a', 'bc'];
        yield ['-ab=c', 'a', 'b=c'];
    }

    public static function nextProvider(): Generator
    {
        yield [
            '-abc',
            [['a', 'bc'], ['b', 'c'], ['c', null]],
        ];

        yield [
            '-a=bc',
            [['a', 'bc']],
        ];

        yield [
            '-ab=c',
            [['a', 'b=c'], ['b', 'c']],
        ];
    }

    /**
     * @param array{string, string|null}[] $allNext
     * @dataProvider nextProvider
     */
    public function testNext(string $arg, array $allNext): void
    {
        $obj = ShortOptArg::fromArg($arg);

        foreach ($allNext as [$name, $value]) {
            self::assertInstanceOf(ShortOptArg::class, $obj);

            self::assertSame($name, $obj->name());
            self::assertSame($value, $obj->value());

            $obj = $obj->next();
        }

        self::assertNull($obj);
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

        $result = ShortOptArg::fromArg($arg);

        self::assertInstanceOf(ShortOptArg::class, $result);
    }

    /** @dataProvider objectFromArgProvider */
    public function testObjectFromArg(string $arg, string $name, ?string $value): void
    {
        $obj = ShortOptArg::fromArg($arg);

        self::assertSame($name, $obj->name());
        self::assertSame($value, $obj->value());
    }
}
