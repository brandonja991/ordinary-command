<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Generator;
use Ordinary\Command\UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Throwable;

class OptionDefinitionTest extends TestCase
{
    public static function fromDefinitionsProvider(): Generator
    {
        yield ['f', [], ['f' => new OptionDefinition('f', ValueRequirement::None)]];
        yield ['', ['foo'], ['foo' => new OptionDefinition('foo', ValueRequirement::None)]];

        yield [
            'abc',
            [],
            [
                'a' => new OptionDefinition('a', ValueRequirement::None),
                'b' => new OptionDefinition('b', ValueRequirement::None),
                'c' => new OptionDefinition('c', ValueRequirement::None),
            ],
        ];

        yield [
            'ab:c::d::e:f',
            [],
            [
                'a' => new OptionDefinition('a', ValueRequirement::None),
                'b' => new OptionDefinition('b', ValueRequirement::Required),
                'c' => new OptionDefinition('c', ValueRequirement::Optional),
                'd' => new OptionDefinition('d', ValueRequirement::Optional),
                'e' => new OptionDefinition('e', ValueRequirement::Required),
                'f' => new OptionDefinition('f', ValueRequirement::None),
            ],
        ];

        yield [
            '',
            ['foo', 'bar:', 'baz::'],
            [
                'bar' => new OptionDefinition('bar', ValueRequirement::Required),
                'baz' => new OptionDefinition('baz', ValueRequirement::Optional),
                'foo' => new OptionDefinition('foo', ValueRequirement::None),
            ],
        ];

        yield [
            'ab:c::',
            ['foo', 'bar:', 'baz::'],
            [
                'a' => new OptionDefinition('a', ValueRequirement::None),
                'b' => new OptionDefinition('b', ValueRequirement::Required),
                'bar' => new OptionDefinition('bar', ValueRequirement::Required),
                'baz' => new OptionDefinition('baz', ValueRequirement::Optional),
                'c' => new OptionDefinition('c', ValueRequirement::Optional),
                'foo' => new OptionDefinition('foo', ValueRequirement::None),
            ],
        ];

        yield ['f', ['f'], UnexpectedValueException::class];
    }

    /**
     * @param string[] $longOpts
     * @param class-string<Throwable>|array<string|int, OptionDefinition> $expected
     * @dataProvider fromDefinitionsProvider
     */
    public function testFromDefinitions(string $shortOpts, array $longOpts, string|array $expected): void
    {
        if (!is_array($expected)) {
            self::expectException($expected);
        }

        $result = OptionDefinition::fromDefinitions($shortOpts, $longOpts);

        self::assertEquals($expected, $result);
    }

    public function testFromString(): void
    {
        $long = OptionDefinition::fromString('foo');
        self::assertSame(OptionType::Long, $long->type);

        $short = OptionDefinition::fromString('f');
        self::assertSame(OptionType::Short, $short->type);

        self::expectException(UnexpectedValueException::class);
        OptionDefinition::fromString('');
    }

    public function testSplitShortOpts(): void
    {
        self::assertSame([], OptionDefinition::splitShortOpts(''));

        self::assertSame(['a'], OptionDefinition::splitShortOpts('a'));
        self::assertSame(['a:'], OptionDefinition::splitShortOpts('a:'));
        self::assertSame(['a::'], OptionDefinition::splitShortOpts('a::'));
        self::assertSame(['a:::'], OptionDefinition::splitShortOpts('a:::'));

        self::assertSame(['a', 'b', 'c'], OptionDefinition::splitShortOpts('abc'));

        self::assertSame(['a:', 'b', 'c'], OptionDefinition::splitShortOpts('a:bc'));
        self::assertSame(['a', 'b:', 'c'], OptionDefinition::splitShortOpts('ab:c'));
        self::assertSame(['a', 'b', 'c:'], OptionDefinition::splitShortOpts('abc:'));
        self::assertSame(['a:', 'b:', 'c'], OptionDefinition::splitShortOpts('a:b:c'));
        self::assertSame(['a', 'b:', 'c:'], OptionDefinition::splitShortOpts('ab:c:'));
        self::assertSame(['a:', 'b', 'c:'], OptionDefinition::splitShortOpts('a:bc:'));

        self::assertSame(['a::', 'b', 'c'], OptionDefinition::splitShortOpts('a::bc'));
        self::assertSame(['a', 'b::', 'c'], OptionDefinition::splitShortOpts('ab::c'));
        self::assertSame(['a', 'b', 'c::'], OptionDefinition::splitShortOpts('abc::'));
        self::assertSame(['a::', 'b::', 'c'], OptionDefinition::splitShortOpts('a::b::c'));
        self::assertSame(['a', 'b::', 'c::'], OptionDefinition::splitShortOpts('ab::c::'));
        self::assertSame(['a::', 'b', 'c::'], OptionDefinition::splitShortOpts('a::bc::'));

        self::assertSame(['a:::', 'b', 'c'], OptionDefinition::splitShortOpts('a:::bc'));
        self::assertSame(['a', 'b:::', 'c'], OptionDefinition::splitShortOpts('ab:::c'));
        self::assertSame(['a', 'b', 'c:::'], OptionDefinition::splitShortOpts('abc:::'));
        self::assertSame(['a:::', 'b:::', 'c'], OptionDefinition::splitShortOpts('a:::b:::c'));
        self::assertSame(['a', 'b:::', 'c:::'], OptionDefinition::splitShortOpts('ab:::c:::'));
        self::assertSame(['a:::', 'b', 'c:::'], OptionDefinition::splitShortOpts('a:::bc:::'));
    }

    public function testMakeSummary(): void
    {
        $result = OptionDefinition::makeSummary([
            new OptionDefinition('foo', ValueRequirement::None, ['f'], 'Foo description'),
            new OptionDefinition('bar', ValueRequirement::None, description: 'Bar description'),
        ]);
        $expected = <<<'SUMMARY'
  --foo, -f    Foo description
  --bar        Bar description
SUMMARY;

        self::assertSame($expected, $result);
    }
}
