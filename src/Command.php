<?php

declare(strict_types=1);

namespace Ordinary\Command;

abstract class Command
{
    use OptionAndArgAccess;
    use StreamHandling;

    abstract public function run(): int;

    abstract public function showHelp(): void;

    /**
     * @param string[] $args
     * @param mixed ...$params Params passed to constructor.
     */
    public static function fromArgs(array $args = [], mixed ...$params): static
    {
        /**
         * @psalm-suppress TooManyArguments
         * @psalm-suppress UnsafeInstantiation
         */
        $obj = new static(...$params);

        return $obj->withArgs($args);
    }

    public function beforeExecute(): ?int
    {
        return null;
    }
}
