<?php

declare(strict_types=1);

namespace Ordinary\Command\Option;

use Generator;
use Ordinary\Command\LogicException;

class Parser
{
    /** @var array<string, Definition> */
    public readonly array $shortOptMap;
    public readonly array $longOptMap;

    public bool $optsOnlyAtStart = true;

    /** @param string[] $longOpts */
    public function __construct(string $shortOpts = '', array $longOpts = [],)
    {
        $this->shortOptMap = array_combine(
            array_map(
                static fn (Definition $definition) => $definition->prefix(),
                $shortOptDefinitions = iterator_to_array(Definition::parseShortOpts($shortOpts)),
            ),
            $shortOptDefinitions,
        );

        $this->longOptMap = array_combine(
            array_map(
                static fn (Definition $definition) => $definition->prefix(),
                $longOptDefinitions = iterator_to_array(Definition::parseLongOpts($longOpts)),
            ),
            $longOptDefinitions,
        );
    }

    /** @param string[] $args */
    public function parse(array $args = []): Generator
    {
        $argv = array_values($args);
        $argc = count($argv);

        for ($i = 0; $i < $argc; $i++) {
            $current = $argv[$i];

            if ($current === '--') {
                yield from array_slice($argv, $i + 1);

                break;
            }

            $optionType = Type::fromOption($current);

            if ($optionType === null) {
                if ($this->optsOnlyAtStart) {
                    yield from array_slice($argv, $i);

                    break;
                }

                yield $current;

                continue;
            }

            yield from match ($optionType) {
                Type::Short => $this->parseShortOptArg($i, $argv),
                Type::Long => $this->parseLongOptArg($i, $argv),
            };
        }
    }

    /** @param string[] $argv */
    private function parseShortOptArg(int &$argIndex, array $argv): Generator
    {
        $arg = $argv[$argIndex];
        assert(
            str_starts_with($arg, '-'),
            new LogicException('Short arg parsing must begin with short arg prefix "-"'),
        );

        $argNoPrefix = substr($arg, 1);
        $argNoPrefixLength = strlen($argNoPrefix);

        for ($i = 0; $i < $argNoPrefixLength; $i++) {
            $definition = $this->shortOptMap['-' . $argNoPrefix[$i]] ?? null;

            if ($definition === null) {
                continue;
            }

            if ($definition->valueRequirement === ValueRequirement::None) {
                yield $definition->name => false;

                continue;
            }

            $inlineValue = substr($argNoPrefix, $i + 1);

            $actualValue = match (true) {
                $inlineValue === '' => null,
                str_starts_with($inlineValue, '=') => substr($inlineValue, 1),
                default => $inlineValue,
            };

            if ($definition->valueRequirement === ValueRequirement::Required && $actualValue === null) {
                $argIndex++;
                $i = $argNoPrefixLength; // set to end of short args if not already

                if (!isset($argv[$argIndex])) {
                    break;
                }

                $actualValue = $argv[$argIndex];
            }

            yield $definition->name => $actualValue ?? false;
        }
    }

    /** @param string[] $argv */
    private function parseLongOptArg(int &$argIndex, array $argv): Generator
    {
        $arg = $argv[$argIndex];
        assert(
            str_starts_with($arg, '--'),
            new LogicException('Long arg parsing must begin with long arg prefix "--"'),
        );

        [$opt, $value] = explode('=', $arg, 2) + [null, null];

        $definition = $this->longOptMap[$opt] ?? null;

        if ($definition === null) {
            return;
        }

        if ($definition->valueRequirement === ValueRequirement::None) {
            yield $definition->name => false;

            return;
        }

        if ($definition->valueRequirement === ValueRequirement::Required && $value === null) {
            $argIndex++;

            if (!isset($argv[$argIndex])) {
                $argIndex--; // reverse index increment and ignore current option

                return;
            }

            $value = $argv[$argIndex];
        }

        yield $definition->name => $value ?? false;
    }
}
