<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Ordinary\Command\UnexpectedValueException;

final class OptionDefinition
{
    public readonly OptionType $type;

    public static function fromString(string $definition): self
    {
        $valueRequirement = ValueRequirement::fromDefinitionString($definition);
        $name = $valueRequirement->extractName($definition);

        return new self($name, $valueRequirement);
    }

    /** @param string[] $aliases */
    public function __construct(
        public readonly string $name,
        public readonly ValueRequirement $valueRequirement = ValueRequirement::None,
        public readonly array $aliases = [],
        public readonly string $description = '',
    ) {
        $this->type = OptionType::fromName($this->name);
    }

    /**
     * @param string[] $longOpts
     * @return array<string, self>
     */
    public static function fromDefinitions(string $shortOpts = '', array $longOpts = []): array
    {
        $shortOptsObjects = array_map(self::fromString(...), self::splitShortOpts($shortOpts));
        $longOptsObjects = array_map(self::fromString(...), $longOpts);

        $shortOptsByName = array_combine(
            array_column($shortOptsObjects, 'name'),
            $shortOptsObjects,
        );
        $longOptsByName = array_combine(
            array_column($longOptsObjects, 'name'),
            $longOptsObjects,
        );

        if ($duplicates = array_intersect_key($shortOptsByName, $longOptsByName)) {
            throw new UnexpectedValueException(
                'Duplicate long and short option definitions found: '
                . implode(', ', array_keys($duplicates)),
            );
        }

        $merged = array_merge($shortOptsByName, $longOptsByName);
        ksort($merged);

        return $merged;
    }

    /** @return string[] */
    public static function splitShortOpts(string $shortOpts): array
    {
        return $shortOpts === '' ? [] : preg_split('/(?<=.)(?=[^:])/', $shortOpts);
    }

    /** @param OptionDefinition[] $definitions */
    public static function makeSummary(array $definitions, int $indent = 2): string
    {
        if (!$definitions) {
            return '';
        }

        $table = [];

        foreach ($definitions as $definition) {
            $prefix = $definition->type->prefix();
            $optArg = $prefix . $definition->name;
            $aliases = array_map(
                static fn (string $name) => OptionType::fromName($name)->prefix() . $name,
                $definition->aliases,
            );

            $table[] = [
                str_repeat(' ', $indent) . implode(', ', [$optArg, ...$aliases]),
                $definition->description,
            ];
        }

        $padLength = max(array_map(strlen(...), array_column($table, 0)));

        $lines = array_map(
            static fn (array $row) => implode('    ', [str_pad($row[0], $padLength), $row[1]]),
            $table,
        );

        return implode("\n", $lines);
    }
}
