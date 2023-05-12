<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

use PHPUnit\Framework\TestCase;

class ArgTypeTest extends TestCase
{
    public function testFromArg(): void
    {
        self::assertSame(ArgType::EndOfOptions, ArgType::fromArg('--'));

        self::assertSame(ArgType::Option, ArgType::fromArg('-'));
        self::assertSame(ArgType::Option, ArgType::fromArg('-f'));
        self::assertSame(ArgType::Option, ArgType::fromArg('-foo'));
        self::assertSame(ArgType::Option, ArgType::fromArg('--f'));
        self::assertSame(ArgType::Option, ArgType::fromArg('--foo'));

        self::assertSame(ArgType::Argument, ArgType::fromArg('foo'));
        self::assertSame(ArgType::Argument, ArgType::fromArg('foo-'));
    }
}
