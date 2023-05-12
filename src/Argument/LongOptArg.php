<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

use Ordinary\Command\Argument\Option\OptionType;
use Ordinary\Command\UnexpectedValueException;

final class LongOptArg implements OptArg
{
    public static function fromArg(string $arg): self
    {
        assert(
            OptionType::tryFromArg($arg) === OptionType::Long,
            new UnexpectedValueException('Failed to create long option from arg: ' . $arg),
        );

        $group = substr($arg, OptionType::Long->prefixLength());
        assert(
            $group !== '',
            new UnexpectedValueException('Failed to create long option arg from empty suffix'),
        );

        return new self($group);
    }

    private function __construct(private readonly string $group)
    {
    }

    public function name(): string
    {
        return $this->hasValue()
            ? strstr($this->group, '=', true)
            : $this->group;
    }

    public function value(): ?string
    {
        return $this->hasValue() ? substr(strstr($this->group, '='), 1) : null;
    }

    public function hasValue(): bool
    {
        return str_contains($this->group, '=');
    }
}
