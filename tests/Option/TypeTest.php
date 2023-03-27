<?php

declare(strict_types=1);

namespace Ordinary\Command\Option;

use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testFromOption(): void
    {
        self::assertSame(Type::Short, Type::fromOption('-foo'));
        self::assertSame(Type::Long, Type::fromOption('--foo'));

        self::assertSame(Type::Short, Type::fromOption('-foo-bar'));
        self::assertSame(Type::Long, Type::fromOption('--foo-bar'));

        self::assertSame(Type::Short, Type::fromOption('-foo--bar'));
        self::assertSame(Type::Long, Type::fromOption('--foo--bar'));

        self::assertSame(Type::Short, Type::fromOption('-f'));
        self::assertSame(Type::Long, Type::fromOption('--f'));

        self::assertSame(Type::Short, Type::fromOption('-'));
        self::assertSame(Type::Long, Type::fromOption('--'));
    }
}
