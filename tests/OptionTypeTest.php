<?php

declare(strict_types=1);

namespace Ordinary\Command;

use PHPUnit\Framework\TestCase;

class OptionTypeTest extends TestCase
{
    public function testFromOption(): void
    {
        self::assertSame(OptionType::Short, OptionType::fromOption('-foo'));
        self::assertSame(OptionType::Long, OptionType::fromOption('--foo'));

        self::assertSame(OptionType::Short, OptionType::fromOption('-foo-bar'));
        self::assertSame(OptionType::Long, OptionType::fromOption('--foo-bar'));

        self::assertSame(OptionType::Short, OptionType::fromOption('-foo--bar'));
        self::assertSame(OptionType::Long, OptionType::fromOption('--foo--bar'));

        self::assertSame(OptionType::Short, OptionType::fromOption('-f'));
        self::assertSame(OptionType::Long, OptionType::fromOption('--f'));

        self::assertSame(OptionType::Short, OptionType::fromOption('-'));
        self::assertSame(OptionType::Long, OptionType::fromOption('--'));
    }
}
