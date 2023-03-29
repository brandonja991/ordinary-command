<?php

declare(strict_types=1);

namespace Ordinary\Command;

use Ordinary\Command\Option\Parser;

trait OptionAndArgAccess
{
    protected string $shortOps = '';

    /** @var string[] */
    protected array $longOpts = [];

    protected bool $optionsOnlyAtStart = true;

    /** @var array<string, string|array|false> */
    private array $options = [];

    /** @var string[] */
    private array $args = [];

    /** @var string[] */
    private array $rawArgs = [];

    /** @return array<string, string|array|false> */
    final public function options(): array
    {
        return $this->options;
    }

    final public function option(string|int $name): string|false|array|null
    {
        return $this->options[$name] ?? null;
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

    /** Get args re-indexed after options extracted */
    final public function arg(int $index): ?string
    {
        return $this->args[$index] ?? null;
    }

    /**
     * Get args indexed in original state before options parsed and extracted.
     *
     * @return string[]
     */
    final public function rawArgs(): array
    {
        return $this->rawArgs;
    }

    /**
     * Get arg indexed in original state before options parsed and extracted.
     */
    final public function rawArg(int $index): ?string
    {
        return $this->rawArgs[$index] ?? null;
    }

    /** @param string[] $args */
    public function withArgs(array $args): static
    {
        $new = clone $this;
        $new->rawArgs = $args;
        $new->parseOptions();

        return $new;
    }

    protected function parseOptions(): void
    {
        $parser = new Parser($this->shortOps, $this->longOpts);
        $parser->optsOnlyAtStart = $this->optionsOnlyAtStart;

        $this->options = [];
        $this->args = [];

        foreach ($parser->parse(array_slice($this->rawArgs(), 1)) as $option => $value) {
            if (is_int($option)) {
                $this->args[] = $value;

                continue;
            }

            if (isset($this->options[$option])) {
                if (!is_array($this->options[$option])) {
                    $this->options[$option] = [$this->options[$option]];
                }

                $this->options[$option][] = $value;

                continue;
            }

            $this->options[$option] = $value;
        }
    }
}
