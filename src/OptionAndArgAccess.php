<?php

declare(strict_types=1);

namespace Ordinary\Command;

use Ordinary\Command\Argument\OptArgParser;
use Ordinary\Command\Argument\Option\OptionRepository;

trait OptionAndArgAccess
{
    protected string $shortOps = '';

    /** @var string[] */
    protected array $longOpts = [];

    private ?OptionRepository $options = null;

    /** @var string[] */
    private array $args = [];

    final public function options(): OptionRepository
    {
        $this->options ??= OptionRepository::create();

        return $this->options;
    }

    /**
     * Get args re-indexed after options extracted
     *
     * @return string[]
     */
    final public function args(): array
    {
        return $this->args;
    }

    final public function scriptName(): string
    {
        return $this->args[0] ?? '';
    }

    /** @param string[] $rawArgs */
    public function withArgs(array $rawArgs): static
    {
        $new = clone $this;
        $new->parseOptions($rawArgs);

        return $new;
    }

    /** @param string[] $rawArgs */
    protected function parseOptions(array $rawArgs): void
    {
        $parser = OptArgParser::fromDefinition($this->shortOps, $this->longOpts);
        [$this->args, $this->options] = $parser->parse($rawArgs);
    }
}
