<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument;

interface OptArg
{
    public function name(): string;

    public function value(): ?string;
}
