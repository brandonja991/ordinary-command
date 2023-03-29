<?php

declare(strict_types=1);

namespace Ordinary\Command;

trait StreamHandling
{
    /** @var ?resource */
    private mixed $stdin = null;

    /** @var ?resource */
    private mixed $stdout = null;

    /** @var ?resource */
    private mixed $stderr = null;

    public function withStreams(
        mixed $stdin = null,
        mixed $stdout = null,
        mixed $stderr = null,
        bool $reset = true,
    ): static {
        $stdin = self::validateStream($stdin);
        $stdout = self::validateStream($stdout);
        $stderr = self::validateStream($stderr);

        $new = clone $this;

        if ($stdin || $reset) {
            $new->stdin = $stdin;
        }

        if ($stdout || $reset) {
            $new->stdout = $stdout;
        }

        if ($stderr || $reset) {
            $new->stderr = $stderr;
        }

        return $new;
    }

    /** @return resource */
    protected function stdin(): mixed
    {
        $this->stdin ??= fopen('/dev/null', 'r');

        return $this->stdin;
    }

    /** @return resource */
    protected function stdout(): mixed
    {
        $this->stdout ??= fopen('/dev/null', 'a');

        return $this->stdout;
    }

    /** @return resource */
    protected function stderr(): mixed
    {
        $this->stderr ??= fopen('/dev/null', 'a');

        return $this->stderr;
    }

    /** @return resource */
    private static function validateStream(mixed $stream): mixed
    {
        if ($stream === null) {
            return null;
        }

        assert(is_resource($stream), new UnexpectedValueException('Non resource given'));
        assert(
            get_resource_type($stream) === 'stream',
            new UnexpectedValueException('Non stream resource given'),
        );

        return $stream;
    }
}
