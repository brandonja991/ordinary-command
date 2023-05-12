<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Ordinary\Command\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

class OptionTypeTest extends TestCase
{
    public function testFromName(): void
    {
        self::assertSame(OptionType::Short, OptionType::fromName('f'));
        self::assertSame(OptionType::Long, OptionType::fromName('fo'));
        self::assertSame(OptionType::Long, OptionType::fromName('foo'));

        self::expectException(UnexpectedValueException::class);
        OptionType::fromName('');
    }

    public function testTryFromArg(): void
    {
        self::assertSame(OptionType::Long, OptionType::tryFromArg('--foo'));
        self::assertSame(OptionType::Long, OptionType::tryFromArg('---foo'));
        self::assertSame(OptionType::Short, OptionType::tryFromArg('-f'));
        self::assertSame(OptionType::Short, OptionType::tryFromArg('-foo'));
    }

    public function testPrefix(): void
    {
        self::assertSame('--', OptionType::Long->prefix());
        self::assertSame('-', OptionType::Short->prefix());
    }

    public function testPrefixLength(): void
    {
        self::assertSame(2, OptionType::Long->prefixLength());
        self::assertSame(1, OptionType::Short->prefixLength());
    }
}
