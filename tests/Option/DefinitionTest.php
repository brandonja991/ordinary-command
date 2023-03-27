<?php

declare(strict_types=1);

namespace Ordinary\Command\Option;

use Generator;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    public static function prefixDefinitionProvider(): Generator
    {
        yield [
            new Definition('f', Type::Short, ValueRequirement::None),
            '-f',
        ];

        yield [
            new Definition('foo', Type::Long, ValueRequirement::None),
            '--foo',
        ];

        yield [
            new Definition('-foo', Type::Long, ValueRequirement::None),
            '---foo',
        ];

        yield [
            new Definition('-foo-bar-', Type::Long, ValueRequirement::None),
            '---foo-bar-',
        ];
    }

    public static function shortOptProvider(): Generator
    {
        yield [
            'a',
            [new Definition('a', Type::Short, ValueRequirement::None)],
        ];

        yield [
            'a:',
            [new Definition('a', Type::Short, ValueRequirement::Required)],
        ];

        yield [
            'a::',
            [new Definition('a', Type::Short, ValueRequirement::Optional)],
        ];

        yield [
            'a:::',
            [new Definition('a:', Type::Short, ValueRequirement::Optional)],
        ];

        $letters = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
        $suffixes = [':', '::'];

        foreach ($letters as $letter) {
            foreach ($suffixes as $suffix) {
                $aRequirement = $letter === 'a' ? ValueRequirement::from($suffix) : ValueRequirement::None;
                $bRequirement = $letter === 'b' ? ValueRequirement::from($suffix) : ValueRequirement::None;
                $cRequirement = $letter === 'c' ? ValueRequirement::from($suffix) : ValueRequirement::None;

                yield [
                    implode('', array_merge($letters, [$letter => $letter . $suffix])),
                    [
                        new Definition('a', Type::Short, $aRequirement),
                        new Definition('b', Type::Short, $bRequirement),
                        new Definition('c', Type::Short, $cRequirement),
                    ],
                ];
            }
        }

        yield [
            'abc',
            [
                new Definition('a', Type::Short, ValueRequirement::None),
                new Definition('b', Type::Short, ValueRequirement::None),
                new Definition('c', Type::Short, ValueRequirement::None),
            ],
        ];

        yield [
            'a:b:c:',
            [
                new Definition('a', Type::Short, ValueRequirement::Required),
                new Definition('b', Type::Short, ValueRequirement::Required),
                new Definition('c', Type::Short, ValueRequirement::Required),
            ],
        ];

        yield [
            'a::b::c::',
            [
                new Definition('a', Type::Short, ValueRequirement::Optional),
                new Definition('b', Type::Short, ValueRequirement::Optional),
                new Definition('c', Type::Short, ValueRequirement::Optional),
            ],
        ];

        yield [
            'a:::b:::c:::',
            [
                new Definition('a:', Type::Short, ValueRequirement::Optional),
                new Definition('b:', Type::Short, ValueRequirement::Optional),
                new Definition('c:', Type::Short, ValueRequirement::Optional),
            ],
        ];
    }

    public static function longOptProvider(): Generator
    {
        yield [
            ['foo'],
            [new Definition('foo', Type::Long, ValueRequirement::None)],
        ];

        yield [
            ['foo:'],
            [new Definition('foo', Type::Long, ValueRequirement::Required)],
        ];

        yield [
            ['foo::'],
            [new Definition('foo', Type::Long, ValueRequirement::Optional)],
        ];

        yield [
            ['foo:::'],
            [new Definition('foo:', Type::Long, ValueRequirement::Optional)],
        ];

        $optNames = ['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'];
        $suffixes = [':', '::'];

        foreach ($optNames as $letter) {
            foreach ($suffixes as $suffix) {
                $aRequirement = $letter === 'foo' ? ValueRequirement::from($suffix) : ValueRequirement::None;
                $bRequirement = $letter === 'bar' ? ValueRequirement::from($suffix) : ValueRequirement::None;
                $cRequirement = $letter === 'baz' ? ValueRequirement::from($suffix) : ValueRequirement::None;

                yield [
                    array_values(array_merge($optNames, [$letter => $letter . $suffix])),
                    [
                        new Definition('foo', Type::Long, $aRequirement),
                        new Definition('bar', Type::Long, $bRequirement),
                        new Definition('baz', Type::Long, $cRequirement),
                    ],
                ];
            }
        }

        yield [
            ['foo', 'bar', 'baz'],
            [
                new Definition('foo', Type::Long, ValueRequirement::None),
                new Definition('bar', Type::Long, ValueRequirement::None),
                new Definition('baz', Type::Long, ValueRequirement::None),
            ],
        ];

        yield [
            ['foo:', 'bar:', 'baz:'],
            [
                new Definition('foo', Type::Long, ValueRequirement::Required),
                new Definition('bar', Type::Long, ValueRequirement::Required),
                new Definition('baz', Type::Long, ValueRequirement::Required),
            ],
        ];

        yield [
            ['foo::', 'bar::', 'baz::'],
            [
                new Definition('foo', Type::Long, ValueRequirement::Optional),
                new Definition('bar', Type::Long, ValueRequirement::Optional),
                new Definition('baz', Type::Long, ValueRequirement::Optional),
            ],
        ];

        yield [
            ['foo:::', 'bar:::', 'baz:::'],
            [
                new Definition('foo:', Type::Long, ValueRequirement::Optional),
                new Definition('bar:', Type::Long, ValueRequirement::Optional),
                new Definition('baz:', Type::Long, ValueRequirement::Optional),
            ],
        ];
    }

    /**
     * @param string[] $longOpts
     * @param array<Definition> $expectedLongOptDefMap
     * @dataProvider longOptProvider
     */
    public function testParseLongOpts(array $longOpts, array $expectedLongOptDefMap): void
    {
        self::assertEquals($expectedLongOptDefMap, iterator_to_array(Definition::parseLongOpts($longOpts)));
    }

    /**
     * @param array<Definition> $expectedShortOptDefMap
     * @dataProvider shortOptProvider
     */
    public function testParseShortOpts(string $shortOpts, array $expectedShortOptDefMap): void
    {
        self::assertEquals($expectedShortOptDefMap, iterator_to_array(Definition::parseShortOpts($shortOpts)));
    }

    /** @dataProvider prefixDefinitionProvider */
    public function testPrefix(Definition $definition, string $expectedPrefix): void
    {
        self::assertSame($expectedPrefix, $definition->prefix());
    }

    public function testSplitShortOpts(): void
    {
        self::assertSame(['a'], Definition::splitShortOpts('a'));
        self::assertSame(['a:'], Definition::splitShortOpts('a:'));
        self::assertSame(['a::'], Definition::splitShortOpts('a::'));
        self::assertSame(['a:::'], Definition::splitShortOpts('a:::'));

        self::assertSame(['a', 'b', 'c'], Definition::splitShortOpts('abc'));

        self::assertSame(['a:', 'b', 'c'], Definition::splitShortOpts('a:bc'));
        self::assertSame(['a', 'b:', 'c'], Definition::splitShortOpts('ab:c'));
        self::assertSame(['a', 'b', 'c:'], Definition::splitShortOpts('abc:'));
        self::assertSame(['a:', 'b:', 'c'], Definition::splitShortOpts('a:b:c'));
        self::assertSame(['a', 'b:', 'c:'], Definition::splitShortOpts('ab:c:'));
        self::assertSame(['a:', 'b', 'c:'], Definition::splitShortOpts('a:bc:'));

        self::assertSame(['a::', 'b', 'c'], Definition::splitShortOpts('a::bc'));
        self::assertSame(['a', 'b::', 'c'], Definition::splitShortOpts('ab::c'));
        self::assertSame(['a', 'b', 'c::'], Definition::splitShortOpts('abc::'));
        self::assertSame(['a::', 'b::', 'c'], Definition::splitShortOpts('a::b::c'));
        self::assertSame(['a', 'b::', 'c::'], Definition::splitShortOpts('ab::c::'));
        self::assertSame(['a::', 'b', 'c::'], Definition::splitShortOpts('a::bc::'));

        self::assertSame(['a:::', 'b', 'c'], Definition::splitShortOpts('a:::bc'));
        self::assertSame(['a', 'b:::', 'c'], Definition::splitShortOpts('ab:::c'));
        self::assertSame(['a', 'b', 'c:::'], Definition::splitShortOpts('abc:::'));
        self::assertSame(['a:::', 'b:::', 'c'], Definition::splitShortOpts('a:::b:::c'));
        self::assertSame(['a', 'b:::', 'c:::'], Definition::splitShortOpts('ab:::c:::'));
        self::assertSame(['a:::', 'b', 'c:::'], Definition::splitShortOpts('a:::bc:::'));
    }
}
