<?php

declare(strict_types=1);

namespace Ordinary\Command;

use Generator;
use PHPUnit\Framework\TestCase;

class OptionAndArgAccessTest extends TestCase
{
    public static function withArgsProvider(): Generator
    {
        yield [['cmd', 'foo'], [], ['foo']];

        yield [
            ['cmd', '-f', '-b', 'b1', '-zz1', '--foo', '--bar', 'bar1', '--baz=baz1', 'foo'],
            [
                'f' => false,
                'b' => 'b1',
                'z' => 'z1',
                'foo' => false,
                'bar' => 'bar1',
                'baz' => 'baz1',
            ],
            ['foo'],
        ];

        yield [
            [
                'cmd',
                '-f', '-f',
                '-b', 'b1', '-bb2', '-b=b3',
                '-zz2', '-z=z3', '-z',
                '--foo', '--foo',
                '--bar', 'bar2', '--bar=bar3',
                '--baz=baz1', '--baz',
                'foo', 'bar', 'baz',
            ],
            [
                'f' => [false, false],
                'b' => ['b1', 'b2', 'b3'],
                'z' => ['z2', 'z3', false],
                'foo' => [false, false],
                'bar' => ['bar2', 'bar3'],
                'baz' => ['baz1', false],
            ],
            ['foo', 'bar', 'baz'],
        ];
    }

    /**
     * @param string[] $args
     * @param array<string, string[]|string> $expectedOptions
     * @param string[] $expectedArgs
     * @dataProvider withArgsProvider
     */
    public function testWithArgs(array $args, array $expectedOptions, array $expectedArgs): void
    {
        $obj = new class () {
            use OptionAndArgAccess;

            public function __construct()
            {
                $this->shortOps = 'fb:z::';
                $this->longOpts = ['foo', 'bar:', 'baz::'];
            }
        };

        $a = $obj->withArgs($args);

        self::assertSame($args, $a->rawArgs());
        self::assertEquals($expectedOptions, $a->options());
        self::assertSame($expectedArgs, $a->args());

        foreach ($expectedOptions as $opt => $optValue) {
            self::assertSame($optValue, $a->option($opt));
        }

        foreach ($expectedArgs as $argI => $argV) {
            self::assertSame($argV, $a->arg($argI));
        }

        foreach ($args as $rawArgI => $rawArgV) {
            self::assertSame($rawArgV, $a->rawArg($rawArgI));
        }
    }
}
