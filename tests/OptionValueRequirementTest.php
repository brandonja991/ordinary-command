<?php

declare(strict_types=1);

namespace Ordinary\Command;

use PHPUnit\Framework\TestCase;

class OptionValueRequirementTest extends TestCase
{
    public function testExtractName(): void
    {
        self::assertSame('foo', OptionValueRequirement::Required->extractName('foo:'));
        self::assertSame('foo', OptionValueRequirement::Optional->extractName('foo::'));

        self::assertSame('foo:bar', OptionValueRequirement::Required->extractName('foo:bar:'));
        self::assertSame('foo:bar', OptionValueRequirement::Optional->extractName('foo:bar::'));

        self::assertSame('foo::bar', OptionValueRequirement::Required->extractName('foo::bar:'));
        self::assertSame('foo::bar', OptionValueRequirement::Optional->extractName('foo::bar::'));
    }

    public function testFromOptionDefinition(): void
    {
        self::assertSame(OptionValueRequirement::Required, OptionValueRequirement::fromOptionDefinition('foo:'));
        self::assertSame(OptionValueRequirement::Optional, OptionValueRequirement::fromOptionDefinition('foo::'));

        self::assertSame(OptionValueRequirement::Required, OptionValueRequirement::fromOptionDefinition('foo:bar:'));
        self::assertSame(OptionValueRequirement::Optional, OptionValueRequirement::fromOptionDefinition('foo:bar::'));

        self::assertSame(OptionValueRequirement::Required, OptionValueRequirement::fromOptionDefinition('foo::bar:'));
        self::assertSame(OptionValueRequirement::Optional, OptionValueRequirement::fromOptionDefinition('foo::bar::'));
    }
}
