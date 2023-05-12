<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Generator;
use Ordinary\Command\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

class ValueRequirementTest extends TestCase
{
    public static function extractFromInvalidNameProvider(): Generator
    {
        yield [ValueRequirement::Optional, 'foo:'];
        yield [ValueRequirement::Optional, 'foo'];
        yield [ValueRequirement::Required, 'foo'];
    }

    public function testExtractName(): void
    {
        self::assertSame('foo', ValueRequirement::Required->extractName('foo:'));
        self::assertSame('foo', ValueRequirement::Optional->extractName('foo::'));
        self::assertSame('foo:', ValueRequirement::Required->extractName('foo::'));

        self::assertSame('foo:bar', ValueRequirement::Required->extractName('foo:bar:'));
        self::assertSame('foo:bar', ValueRequirement::Optional->extractName('foo:bar::'));

        self::assertSame('foo::bar', ValueRequirement::Required->extractName('foo::bar:'));
        self::assertSame('foo::bar', ValueRequirement::Optional->extractName('foo::bar::'));
    }

    /** @dataProvider extractFromInvalidNameProvider */
    public function testExtractFromInvalidName(ValueRequirement $valueRequirement, string $name): void
    {
        self::expectException(UnexpectedValueException::class);
        $valueRequirement->extractName($name);
    }

    public function testFromOptionDefinition(): void
    {
        self::assertSame(ValueRequirement::Required, ValueRequirement::fromDefinitionString('foo:'));
        self::assertSame(ValueRequirement::Optional, ValueRequirement::fromDefinitionString('foo::'));

        self::assertSame(ValueRequirement::Required, ValueRequirement::fromDefinitionString('foo:bar:'));
        self::assertSame(ValueRequirement::Optional, ValueRequirement::fromDefinitionString('foo:bar::'));

        self::assertSame(ValueRequirement::Required, ValueRequirement::fromDefinitionString('foo::bar:'));
        self::assertSame(ValueRequirement::Optional, ValueRequirement::fromDefinitionString('foo::bar::'));
    }
}
