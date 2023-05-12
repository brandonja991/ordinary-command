<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

use Generator;
use Ordinary\Command\Argument\Option\OptionDefinition;
use Ordinary\Command\Argument\Option\OptionRepository;
use Ordinary\Command\Argument\Option\OptionType;
use Ordinary\Command\Argument\Option\ValueRequirement;
use Ordinary\Command\LogicException;

class OptArgParser
{
    private readonly array $aliasMap;

    /** @param OptionDefinition[] $options */
    public static function fromOptions(array $options): self
    {
        $final = [];

        foreach ($options as $index => $option) {
            /** @psalm-suppress RedundantConditionGivenDocblockType */
            assert(
                $option instanceof OptionDefinition,
                new LogicException('Option at index ' . $index . ' was not an Option instance'),
            );

            if (isset($final[$option->name])) {
                throw new LogicException('Duplicate option name provided at index ' . $index);
            }

            $final[$option->name] = $option;
        }

        return new self($final);
    }

    /** @param string[] $longOpts */
    public static function fromDefinition(string $shortOpts = '', array $longOpts = []): self
    {
        return new self(OptionDefinition::fromDefinitions($shortOpts, $longOpts));
    }

    /** @param array<string, OptionDefinition> $options */
    private function __construct(private readonly array $options = [])
    {
        $aliasMap = [];

        /**
         * @var string $name
         * @var OptionDefinition $option
         */
        foreach ($this->options as $name => $option) {
            if (!$option->aliases) {
                continue;
            }

            $aliasesToAdd = array_fill_keys(array_unique($option->aliases), $name);

            if ($aliasOverlap = array_intersect_key($aliasMap, $aliasesToAdd)) {
                $aliasOverlap = array_merge(array_values($aliasOverlap), [$name]);

                throw new LogicException(
                    'Overlapping aliases found between options: ' . implode(', ', $aliasOverlap),
                );
            }

            $aliasMap = array_merge($aliasMap, $aliasesToAdd);
        }

        if ($optionAliasOverlap = array_intersect_key($this->options, $aliasMap)) {
            throw new LogicException(
                'Overlapping option+aliases encountered: '
                . implode(', ', array_keys($optionAliasOverlap)),
            );
        }

        $this->aliasMap = $aliasMap;
    }

    /**
     * @param string[] $rawArgs
     * @return array{string[], OptionRepository}
     */
    public function parse(array $rawArgs): array
    {
        $opts = [];
        $args = [];

        /** @var ArgType $type */
        foreach ($this->rawArgsParser($rawArgs) as [$type, $values]) {
            if (in_array($type, [ArgType::Argument, ArgType::ScriptName], true)) {
                // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
                /** @var string[] $values */
                $args = array_merge($args, $values);

                continue;
            }

            // else is options
            // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
            /** @var array{string, string|false}[] $values */

            foreach ($values as [$optName, $optValue]) {
                $opts[$optName] ??= [];
                $opts[$optName][] = $optValue;
            }
        }

        return [$args, OptionRepository::create($opts)];
    }

    /**
     * @param string[] $rawArgs
     * @return Generator<array{ArgType, string[]|array{string, string|false}[]}>
     */
    private function rawArgsParser(array $rawArgs): Generator
    {
        $rawArgs = array_values($rawArgs);
        $rawArgsLength = count($rawArgs);

        if (isset($rawArgs[0])) {
            yield [ArgType::ScriptName, [$rawArgs[0]]];
        }

        for ($i = 1; $i < $rawArgsLength; $i++) {
            if ($rawArgs[$i] === '--') {
                yield [ArgType::Argument, array_slice($rawArgs, $i + 1)];

                break;
            }

            if (!str_starts_with($rawArgs[$i], '-')) {
                yield [ArgType::Argument, array_slice($rawArgs, $i)];

                break;
            }

            $option = match (OptionType::tryFromArg($rawArgs[$i])) {
                OptionType::Short => ShortOptArg::fromArg($rawArgs[$i]),
                OptionType::Long => LongOptArg::fromArg($rawArgs[$i]),
            };

            $optionValueList = match (true) {
                $option instanceof LongOptArg => $this->parseLongOption($option),
                $option instanceof ShortOptArg => $this->parseShortOption($option),
            };

            $last = array_pop($optionValueList);

            if ($optionValueList) {
                yield [ArgType::Option, $optionValueList];
            }

            if ($last === null) {
                continue;
            }

            [$lastName, $lastValue] = $last;
            $definition = $this->options[$lastName];

            if ($lastValue !== false || $definition->valueRequirement !== ValueRequirement::Required) {
                yield [ArgType::Option, [$last]];

                continue;
            }

            $i++;

            if (!isset($rawArgs[$i])) {
                break;
            }

            $last[1] = $rawArgs[$i];

            yield [ArgType::Option, [$last]];
        }
    }

    /** @return array{string, string|false}[] */
    private function parseLongOption(LongOptArg $option): array
    {
        $name = $option->name();
        $definition = $this->options[$name] ?? null;
        $definition ??= isset($this->aliasMap[$name]) ? ($this->options[$this->aliasMap[$name]] ?? null) : null;

        if ($definition === null) {
            return [];
        }

        $name = $definition->name;

        if ($definition->valueRequirement === ValueRequirement::None) {
            return [[$name, false]];
        }

        return [[$name, $option->value() ?? false]];
    }

    /** @return array{string, string|false}[] */
    private function parseShortOption(ShortOptArg $option): array
    {
        $result = [];

        while ($option) {
            $name = $option->name();
            $definition = $this->options[$name] ?? null;
            $definition ??= isset($this->aliasMap[$name]) ? ($this->options[$this->aliasMap[$name]] ?? null) : null;

            if ($definition === null) {
                $option = $option->next();

                continue;
            }

            $name = $definition->name;

            if ($definition->valueRequirement === ValueRequirement::None) {
                $result[] = [$name, false];
                $option = $option->next();

                continue;
            }

            $result[] = [$name, $option->value() ?? false];

            break;
        }

        return $result;
    }
}
