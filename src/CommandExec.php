<?php

declare(strict_types=1);

namespace Ordinary\Command;

class CommandExec
{
    /** @var string[] */
    public array $helpOptions = ['h', 'help'];

    /** @var string[] */
    public array $args = [];

    public function execute(Command $cmd): int
    {
        $cmd = $cmd->withArgs($this->args);

        $beforeExecute = $cmd->beforeExecute();

        if (is_int($beforeExecute)) {
            return $beforeExecute;
        }

        if (array_intersect_key($cmd->options(), array_flip($this->helpOptions))) {
            $cmd->showHelp();

            return 0;
        }

        return $cmd->run();
    }
}
