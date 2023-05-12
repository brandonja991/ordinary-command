<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

enum ArgType
{
    case Option;
    case Argument;
    case EndOfOptions;

    case ScriptName;

    public static function fromArg(string $arg): self
    {
        return match (true) {
            $arg === '--' => self::EndOfOptions,
            str_starts_with($arg, '-') => self::Option,
            default => self::Argument,
        };
    }
}
