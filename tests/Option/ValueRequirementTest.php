<?php

declare(strict_types=1);

namespace Ordinary\Command\Option;

use PHPUnit\Framework\TestCase;

class ValueRequirementTest extends TestCase
{
    public function testExtractName(): void
    {
        self::assertSame('foo', ValueRequirement::Required->extractName('foo:'));
        self::assertSame('foo', ValueRequirement::Optional->extractName('foo::'));

        self::assertSame('foo:bar', ValueRequirement::Required->extractName('foo:bar:'));
        self::assertSame('foo:bar', ValueRequirement::Optional->extractName('foo:bar::'));

        self::assertSame('foo::bar', ValueRequirement::Required->extractName('foo::bar:'));
        self::assertSame('foo::bar', ValueRequirement::Optional->extractName('foo::bar::'));
    }

    public function testFromOptionDefinition(): void
    {
        self::assertSame(ValueRequirement::Required, ValueRequirement::fromOptionDefinition('foo:'));
        self::assertSame(ValueRequirement::Optional, ValueRequirement::fromOptionDefinition('foo::'));

        self::assertSame(ValueRequirement::Required, ValueRequirement::fromOptionDefinition('foo:bar:'));
        self::assertSame(ValueRequirement::Optional, ValueRequirement::fromOptionDefinition('foo:bar::'));

        self::assertSame(ValueRequirement::Required, ValueRequirement::fromOptionDefinition('foo::bar:'));
        self::assertSame(ValueRequirement::Optional, ValueRequirement::fromOptionDefinition('foo::bar::'));
    }
}
