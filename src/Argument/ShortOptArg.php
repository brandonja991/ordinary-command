<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

use Ordinary\Command\Argument\Option\OptionType;
use Ordinary\Command\UnexpectedValueException;

final class ShortOptArg implements OptArg
{
    public static function fromArg(string $arg): self
    {
        assert(
            OptionType::tryFromArg($arg) === OptionType::Short,
            new UnexpectedValueException('Failed to create short option from arg: ' . $arg),
        );

        $group = substr($arg, OptionType::Short->prefixLength());
        assert(
            $group !== '',
            new UnexpectedValueException('Failed to create short option arg from empty suffix'),
        );

        return new self($group);
    }

    private function __construct(private readonly string $group)
    {
    }

    public function next(): ?self
    {
        $suffix = $this->suffix();

        if ($suffix === '' || str_starts_with($suffix, '=')) {
            return null;
        }

        return new self($suffix);
    }

    public function name(): string
    {
        return substr($this->group, 0, 1);
    }

    public function value(): ?string
    {
        $suffix = $this->suffix();

        if ($suffix === '') {
            return null;
        }

        return str_starts_with($suffix, '=') ? substr($suffix, 1) : $suffix;
    }

    private function suffix(): string
    {
        return substr($this->group, 1);
    }
}
