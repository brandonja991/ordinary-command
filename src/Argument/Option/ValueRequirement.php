<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Ordinary\Command\UnexpectedValueException;

enum ValueRequirement: string
{
    case Optional = '::';

    case Required = ':';

    case None = '';

    public static function fromDefinitionString(string $definition): self
    {
        return match (true) {
            str_ends_with($definition, '::') => self::Optional,
            str_ends_with($definition, ':') => self::Required,
            default => self::None,
        };
    }

    public function extractName(string $definition): string
    {
        if (!str_ends_with($definition, $this->value)) {
            throw new UnexpectedValueException('Failed to extract name from option definition string');
        }

        return match ($this) {
            self::Optional => substr($definition, 0, -2),
            self::Required => substr($definition, 0, -1),
            self::None => $definition,
        };
    }
}
