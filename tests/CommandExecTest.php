<?php

declare(strict_types=1);

namespace Ordinary\Command;

use Generator;
use PHPUnit\Framework\TestCase;

class CommandExecTest extends TestCase
{
    public static function executeResultProvider(): Generator
    {
        yield [null, 0, 0];
        yield [null, 1, 1];
        yield [0, 1, 0];
        yield [2, 1, 2];
    }

    public function testExecuteCallOrder(): void
    {
        $expectedCallOrder = ['beforeExecute', 'run'];
        $callOrder = [];

        $cmd = self::createMock(Command::class);
        $cmd->method('run')->willReturnCallback(static function () use (&$callOrder) {
            $callOrder[] = 'run';

            return 0;
        });

        $cmd->method('beforeExecute')->willReturnCallback(static function () use (&$callOrder) {
            $callOrder[] = 'beforeExecute';

            return null;
        });

        $cmd->method('withArgs')->willReturn($cmd);

        $exec = new CommandExec();
        $exec->execute($cmd);

        self::assertSame($expectedCallOrder, $callOrder);
    }

    /** @dataProvider executeResultProvider */
    public function testExecuteResult(?int $beforeExecute, int $run, int $expected): void
    {
        $cmd = self::createMock(Command::class);
        $cmd->method('run')->willReturn($run);
        $cmd->method('beforeExecute')->willReturn($beforeExecute);
        $cmd->method('withArgs')->willReturn($cmd);

        $exec = new CommandExec();
        self::assertSame($expected, $exec->execute($cmd));
    }
}
