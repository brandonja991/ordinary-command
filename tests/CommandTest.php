<?php

declare(strict_types=1);

namespace Ordinary\Command;

use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testFromArgs(): void
    {
        $cmd = new class () extends Command {
            public readonly array $params;

            public function __construct(mixed ...$params)
            {
                $this->params = $params;
            }

            public function showHelp(): void
            {
                // do nothing
            }

            public function run(): int
            {
                return 0;
            }
        };

        $args = [];
        $params = [];

        $new = $cmd::fromArgs($args, ...$params);

        self::assertNotSame($cmd, $new);
        self::assertSame($args, $new->rawArgs());
        self::assertSame($params, $new->params);
    }
}
