<?php

declare(strict_types=1);

namespace Ordinary\Command;

abstract class Command
{
    use OptionAndArgAccess;
    use StreamHandling;

    abstract public function run(): int;

    abstract public function showHelp(): void;

    public function beforeExecute(): ?int
    {
        return null;
    }
}
