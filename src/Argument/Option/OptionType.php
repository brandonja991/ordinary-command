<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Ordinary\Command\UnexpectedValueException;

enum OptionType: string
{
    case Short = '-';

    case Long = '--';

    public static function fromName(string $name): self
    {
        $len = strlen($name);

        return match (true) {
            $len > 1 => self::Long,
            $len === 1 => self::Short,
            default => throw new UnexpectedValueException('Failed to get option type from empty name'),
        };
    }

    public static function tryFromArg(string $arg): ?self
    {
        return match (true) {
            str_starts_with($arg, self::Long->prefix()) => self::Long,
            str_starts_with($arg, self::Short->prefix()) => self::Short,
            default => null,
        };
    }

    public function prefix(): string
    {
        return $this->value;
    }

    public function prefixLength(): int
    {
        return strlen($this->value);
    }
}
