<?php

declare(strict_types=1);

namespace Ordinary\Command\Option;

use Generator;

class Definition
{
    public function __construct(
        public readonly string $name,
        public readonly Type $type,
        public readonly ValueRequirement $valueRequirement,
    ) {
    }

    /** @return string[] */
    public static function splitShortOpts(string $shortOpts): array
    {
        return preg_split('/(?<=.)(?=[^:])/', $shortOpts);
    }

    public static function parseShortOpts(string $shortOpts): Generator
    {
        $shortOptsList = self::splitShortOpts($shortOpts);

        return self::parseOptionList(Type::Short, $shortOptsList);
    }

    /** @param string[] $longOpts */
    public static function parseLongOpts(array $longOpts): Generator
    {
        return self::parseOptionList(Type::Long, $longOpts);
    }

    public function prefix(): string
    {
        return $this->type->value . $this->name;
    }

    /**
     * @param string[] $optionDefinitionList
     * @return Generator<self>
     */
    private static function parseOptionList(Type $type, array $optionDefinitionList): Generator
    {
        foreach ($optionDefinitionList as $optionDefinition) {
            $valueRequirement = ValueRequirement::fromOptionDefinition($optionDefinition);
            $name = $valueRequirement->extractName($optionDefinition);

            yield new self($name, $type, $valueRequirement);
        }
    }
}
