<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

use Ordinary\Command\Argument\Option\OptionDefinition;
use Ordinary\Command\Argument\Option\OptionRepository;
use Ordinary\Command\Argument\Option\ValueRequirement;
use PHPUnit\Framework\TestCase;

class OptArgParserTest extends TestCase
{
    public function testParse(): void
    {
        $parserFromDefinition = OptArgParser::fromDefinition('fb:z::', ['foo', 'bar:', 'baz::']);
        $parserFromOptions = OptArgParser::fromOptions([
            new OptionDefinition('f', ValueRequirement::None),
            new OptionDefinition('b', ValueRequirement::Required),
            new OptionDefinition('z', ValueRequirement::Optional),
            new OptionDefinition('foo', ValueRequirement::None),
            new OptionDefinition('bar', ValueRequirement::Required),
            new OptionDefinition('baz', ValueRequirement::Optional),
        ]);

        $args = [
            'cmd',
            '-f', '-f',
            '-b', 'b1', '-bb2', '-b=b3',
            '-zz2', '-z=z3', '-z',
            '--foo', '--foo',
            '--bar', 'bar2', '--bar=bar3',
            '--baz=baz1', '--baz',
            'foo', 'bar', 'baz',
        ];

        $repo = OptionRepository::create([
            'f' => [false, false],
            'b' => ['b1', 'b2', 'b3'],
            'z' => ['z2', 'z3', false],
            'foo' => [false, false],
            'bar' => ['bar2', 'bar3'],
            'baz' => ['baz1', false],
        ]);

        [$argsFromDef, $optsFromParsedDef] = $parserFromDefinition->parse($args);

        self::assertSame(['cmd', 'foo', 'bar', 'baz'], $argsFromDef);

        self::assertSame($repo->getArray('f'), $optsFromParsedDef->getArray('f'));
        self::assertSame($repo->getArray('b'), $optsFromParsedDef->getArray('b'));
        self::assertSame($repo->getArray('z'), $optsFromParsedDef->getArray('z'));
        self::assertSame($repo->getArray('foo'), $optsFromParsedDef->getArray('foo'));
        self::assertSame($repo->getArray('bar'), $optsFromParsedDef->getArray('bar'));
        self::assertSame($repo->getArray('baz'), $optsFromParsedDef->getArray('baz'));

        [$argsFromOpts, $optsFromParsedOpts] = $parserFromOptions->parse($args);

        self::assertSame($argsFromDef, $argsFromOpts);

        self::assertSame($repo->getArray('f'), $optsFromParsedOpts->getArray('f'));
        self::assertSame($repo->getArray('b'), $optsFromParsedOpts->getArray('b'));
        self::assertSame($repo->getArray('z'), $optsFromParsedOpts->getArray('z'));
        self::assertSame($repo->getArray('foo'), $optsFromParsedOpts->getArray('foo'));
        self::assertSame($repo->getArray('bar'), $optsFromParsedOpts->getArray('bar'));
        self::assertSame($repo->getArray('baz'), $optsFromParsedOpts->getArray('baz'));
    }
}
