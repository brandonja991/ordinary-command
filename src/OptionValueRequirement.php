<?php

declare(strict_types=1);

namespace Ordinary\Command;

enum OptionValueRequirement: string
{
    case Optional = '::';

    case Required = ':';

    case None = '';

    public static function fromOptionDefinition(string $definition): self
    {
        return match (true) {
            str_ends_with($definition, '::') => self::Optional,
            str_ends_with($definition, ':') => self::Required,
            default => self::None,
        };
    }

    public function extractName(string $definition): string
    {
        return match ($this) {
            self::Optional => substr($definition, 0, -2),
            self::Required => substr($definition, 0, -1),
            self::None => $definition,
        };
    }
}
