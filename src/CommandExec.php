<?php

declare(strict_types=1);

namespace Ordinary\Command;

class CommandExec
{
    public string $helpOption = 'help';

    /** @var string[] */
    public ?array $args = null;

    public function execute(Command $cmd): int
    {
        if ($this->args !== null) {
            $cmd = $cmd->withArgs($this->args);
        }

        $beforeExecute = $cmd->beforeExecute();

        if (is_int($beforeExecute)) {
            return $beforeExecute;
        }

        if ($this->helpOption !== '' && $cmd->options()->exists($this->helpOption)) {
            $cmd->showHelp();

            return 0;
        }

        return $cmd->run();
    }
}
