<?php

declare(strict_types=1);

namespace Ordinary\Command\Option;

use Generator;
use Iterator;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /** @param iterable<array{?string, string}> $expectedItems */
    public static function assertExpectedItems(iterable $expectedItems, Iterator $iterator): void
    {
        $iterator->rewind();

        foreach ($expectedItems as [$name, $value]) {
            self::assertTrue($iterator->valid());

            if ($name !== null) {
                self::assertSame($name, $iterator->key());
            }

            self::assertSame($value, $iterator->current());

            $iterator->next();
        }

        self::assertFalse($iterator->valid());
    }

    public static function duplicateOptionsProvider(): Generator
    {
        yield [
            ['-fff'],
            [['f', false], ['f', false], ['f', false]],
        ];

        yield [
            ['-f', '-f', '-f'],
            [['f', false], ['f', false], ['f', false]],
        ];

        yield [
            ['--foo', '--foo', '--foo'],
            [['foo', false], ['foo', false], ['foo', false]],
        ];

        yield [
            ['-bb1', '-b=b2', '-b', 'b3'],
            [['b', 'b1'], ['b', 'b2'], ['b', 'b3']],
        ];

        yield [
            ['--bar=bar1', '--bar', 'bar2'],
            [['bar', 'bar1'], ['bar', 'bar2']],
        ];

        yield [
            ['-z', '-zz2', '-z=z3'],
            [['z', false], ['z', 'z2'], ['z', 'z3']],
        ];

        yield [
            ['--baz', '--baz=baz2'],
            [['baz', false], ['baz', 'baz2']],
        ];
    }

    public static function parseArgsOnlyProvider(): Generator
    {
        yield [[], []];
        yield [['-f'], []];
        yield [['--foo'], []];
        yield [['-f', '--foo'], []];
        yield [['--foo', '-f'], []];
        yield [['foo'], ['foo']];

        yield [['-f', '-b'], []];
        yield [['--foo', '--bar'], []];
        yield [['--foo', '-f', '--bar', '-b'], []];
        yield [['foo', 'bar'], ['foo', 'bar']];
        yield [['-f', '--foo', '--bar', '-b', 'foo', 'bar'], ['foo', 'bar']];

        yield [['--'], []];
        yield [['--', '--foo'], ['--foo']];
        yield [['--', '-f'], ['-f']];
        yield [['--', '-f', '--foo', 'foo'], ['-f', '--foo', 'foo']];
    }

    public static function parseOnlyOptsAtStartProvider(): Generator
    {
        yield [
            true,
            ['-f', '--bar', 'barValue', 'arg1'],
            [
                ['f', false],
                ['bar', 'barValue'],
                [null, 'arg1'],
            ],
        ];

        yield [
            true,
            ['arg1', '-f', '--bar', 'barValue'],
            [
                [null, 'arg1'],
                [null, '-f'],
                [null, '--bar'],
                [null, 'barValue'],
            ],
        ];

        yield [
            true,
            ['--bar', 'barValue', 'arg1', '-f'],
            [
                ['bar', 'barValue'],
                [null, 'arg1'],
                [null, '-f'],
            ],
        ];

        // false

        yield [
            false,
            ['-f', '--bar', 'barValue', 'arg1'],
            [
                ['f', false],
                ['bar', 'barValue'],
                [null, 'arg1'],
            ],
        ];

        yield [
            false,
            ['arg1', '-f', '--bar', 'barValue'],
            [
                [null, 'arg1'],
                ['f', false],
                ['bar', 'barValue'],
            ],
        ];

        yield [
            false,
            ['--bar', 'barValue', 'arg1', '-f'],
            [
                ['bar', 'barValue'],
                [null, 'arg1'],
                ['f', false],
            ],
        ];
    }

    public static function parseOptsProvider(): Generator
    {
        yield [ // single short
            'f',
            [],
            ['-f'],
            [
                ['f', false],
            ],
        ];

        yield [ // single long
            '',
            ['foo'],
            ['--foo'],
            [
                ['foo', false],
            ],
        ];

        yield [ // multi short
            'f',
            [],
            ['-f', '-f'],
            [
                ['f', false],
                ['f', false],
            ],
        ];

        yield [ // multi long
            '',
            ['foo'],
            ['--foo', '--foo'],
            [
                ['foo', false],
                ['foo', false],
            ],
        ];

        yield [ // valid short only w/ given long
            'f',
            [],
            ['-f', '--f'],
            [
                ['f', false],
            ],
        ];

        yield [ // valid long only w/ given short
            '',
            ['foo'],
            ['--foo', '-f'],
            [
                ['foo', false],
            ],
        ];

        yield [ // valid long and short opts w/ both given
            'f',
            ['f'],
            ['-f', '--f'],
            [
                ['f', false],
                ['f', false],
            ],
        ];
    }

    public static function parseValuesProvider(): Generator
    {
        // required
        yield [ // short with req value - separate args
            'f:',
            [],
            ['-f', 'bar'],
            [
                ['f', 'bar'],
            ],
        ];

        yield [ // short with req value - same arg w/ equal
            'f:',
            [],
            ['-f=bar'],
            [
                ['f', 'bar'],
            ],
        ];

        yield [ // short with req value - same arg w/o equal
            'f:',
            [],
            ['-fbar'],
            [
                ['f', 'bar'],
            ],
        ];

        yield [ // long with req value - separate args
            '',
            ['foo:'],
            ['--foo', 'bar'],
            [
                ['foo', 'bar'],
            ],
        ];

        yield [ // long with req value - same arg w/ equal
            '',
            ['foo:'],
            ['--foo=bar'],
            [
                ['foo', 'bar'],
            ],
        ];

        yield [ // long with req value - same arg w/o equal
            '',
            ['foo:'],
            ['--foobar'],
            [],
        ];

        // optional
        yield [ // short with optional value - no value
            'f::',
            [],
            ['-f'],
            [
                ['f', false],
            ],
        ];

        yield [ // short with optional value - separate args
            'f::',
            [],
            ['-f', 'bar'],
            [
                ['f', false],
                [null, 'bar'],
            ],
        ];

        yield [ // short with optional value - same arg w/ equal
            'f::',
            [],
            ['-f=bar'],
            [
                ['f', 'bar'],
            ],
        ];

        yield [ // short with optional value - same arg w/o equal
            'f::',
            [],
            ['-fbar'],
            [
                ['f', 'bar'],
            ],
        ];

        yield [ // long with optional value - no arg
            '',
            ['foo::'],
            ['--foo'],
            [
                ['foo', false],
            ],
        ];

        yield [ // long with optional value - separate args
            '',
            ['foo::'],
            ['--foo', 'bar'],
            [
                ['foo', false],
                [null, 'bar'],
            ],
        ];

        yield [ // long with optional value - same arg w/ equal
            '',
            ['foo::'],
            ['--foo=bar'],
            [
                ['foo', 'bar'],
            ],
        ];

        yield [ // long with optional value - same arg w/o equal
            '',
            ['foo:'],
            ['--foobar'],
            [],
        ];
    }

    /**
     * @param string[] $longOpts
     * @param string[] $args
     * @param array<array{?string, string}> $expectedItems
     * @dataProvider parseOptsProvider
     * @dataProvider parseValuesProvider
     */
    public function testParseOpts(
        string $shortOpts,
        array $longOpts,
        array $args,
        array $expectedItems,
    ): void {
        $parser = new Parser($shortOpts, $longOpts);
        self::assertExpectedItems($expectedItems, $parser->parse($args));
    }

    /**
     * @param string[] $inputArgs
     * @param string[] $expectedArgs
     * @dataProvider parseArgsOnlyProvider
     */
    public function testParseArgsOnly(array $inputArgs, array $expectedArgs): void
    {
        $parser = new Parser();
        self::assertSame($expectedArgs, iterator_to_array($parser->parse($inputArgs)));
    }

    /**
     * @param string[] $args
     * @param array<array{?string, string}> $expectedItems
     * @dataProvider parseOnlyOptsAtStartProvider
     */
    public function testParseOptsOnlyAtStart(bool $optsOnlyAtStart, array $args, array $expectedItems): void
    {
        $parser = new Parser('fb:z::', ['foo', 'bar:', 'baz::']);
        $parser->optsOnlyAtStart = $optsOnlyAtStart;
        self::assertExpectedItems($expectedItems, $parser->parse($args));
    }

    /**
     * @param string[] $args
     * @param array<array{?string, string}> $expectedItems
     * @dataProvider duplicateOptionsProvider
     */
    public function testDuplicateOptions(array $args, array $expectedItems): void
    {
        $parser = new Parser('fb:z::', ['foo', 'bar:', 'baz::']);
        self::assertExpectedItems($expectedItems, $parser->parse($args));
    }
}
