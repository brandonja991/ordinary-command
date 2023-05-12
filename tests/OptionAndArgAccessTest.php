<?php

declare(strict_types=1);

namespace Ordinary\Command;

use PHPUnit\Framework\TestCase;

class OptionAndArgAccessTest extends TestCase
{
    public function testWithArgs(): void
    {
        $argSet1 = ['cmd', 'foo', 'bar'];
        $obj = new class ($argSet1) {
            use OptionAndArgAccess;

            /** @param string[] $argSet1 */
            public function __construct(array $argSet1)
            {
                $this->shortOps = 'fb:z::';
                $this->longOpts = ['foo', 'bar:', 'baz::'];
                $this->parseOptions($argSet1);
            }
        };

        self::assertSame($argSet1, $obj->args());
        self::assertSame([], $obj->options()->all());
        self::assertSame('cmd', $obj->scriptName());

        $a = $obj->withArgs($argSet1);
        self::assertNotSame($obj, $a);
        self::assertNotSame($obj->options(), $a->options());
        self::assertSame($obj->args(), $a->args());
        self::assertSame($obj->options()->all(), $a->options()->all());
        self::assertSame($obj->scriptName(), $a->scriptName());

        $argSet2 = [
            'cmd2',
            '-f', '-f',
            '-b', 'b1', '-bb2', '-b=b3',
            '-zz2', '-z=z3', '-z',
            '--foo', '--foo',
            '--bar', 'bar2', '--bar=bar3',
            '--baz=baz1', '--baz',
            'foo', 'bar', 'baz',
        ];

        $b = $a->withArgs($argSet2);
        self::assertNotSame($a, $b);
        self::assertNotSame($a->options(), $optionsB = $b->options());
        self::assertSame('cmd2', $b->scriptName());

        self::assertSame([false, false], $optionsB->getArray('f'));
        self::assertSame(['b1', 'b2', 'b3'], $optionsB->getArray('b'));
        self::assertSame(['z2', 'z3', false], $optionsB->getArray('z'));

        self::assertSame([false, false], $optionsB->getArray('foo'));
        self::assertSame(['bar2', 'bar3'], $optionsB->getArray('bar'));
        self::assertSame(['baz1', false], $optionsB->getArray('baz'));

        self::assertSame(['cmd2', 'foo', 'bar', 'baz'], $b->Args());
    }
}
