<?php

declare(strict_types=1);

namespace Ordinary\Command;

enum OptionType: string
{
    case Short = '-';

    case Long = '--';

    public static function fromOption(string $option): ?self
    {
        return match (true) {
            str_starts_with($option, '--') => self::Long,
            str_starts_with($option, '-') => self::Short,
            default => null,
        };
    }
}
