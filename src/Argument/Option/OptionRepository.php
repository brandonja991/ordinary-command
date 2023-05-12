<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Ordinary\Command\UnexpectedValueException;

class OptionRepository
{
    /** @param array<string|int, string|false|array<string|false>> $options */
    public static function create(array $options = []): self
    {
        foreach ($options as $name => $option) {
            if (is_array($option)) {
                foreach ($option as $duplicate) {
                    /** @psalm-suppress RedundantConditionGivenDocblockType */
                    assert(
                        is_string($duplicate) || $duplicate === false,
                        new UnexpectedValueException(
                            'Unexpected value for option: ' . $name . ' => ' . get_debug_type($duplicate),
                        ),
                    );
                }

                continue;
            }

            /** @psalm-suppress RedundantConditionGivenDocblockType */
            assert(
                is_string($option) || $option === false,
                new UnexpectedValueException(
                    'Unexpected value for option: ' . $name . ' => ' . get_debug_type($option),
                ),
            );
        }

        return new self($options);
    }

    /** @param array<string|int, string|false|array<string|false>> $options */
    private function __construct(private readonly array $options)
    {
    }

    /** @return array<string|int, string|false|array<string|false>> */
    public function all(): array
    {
        return $this->options;
    }

    public function get(string $name): string|array|false|null
    {
        return $this->options[$name] ?? null;
    }

    /** @return array<string|false> */
    public function getArray(string $name): array
    {
        $value = $this->get($name);

        return match (true) {
            $value === null => [],
            is_array($value) => $value,
            default => [$value],
        };
    }

    public function getFirst(string $name): string|false|null
    {
        $value = $this->get($name);

        if (!is_array($value)) {
            return $value;
        }

        $firstIndex = array_key_first($value);

        return $firstIndex === null ? null : $value[$firstIndex];
    }

    public function getLast(string $name): string|false|null
    {
        $value = $this->get($name);

        if (!is_array($value)) {
            return $value;
        }

        $lastIndex = array_key_last($value);

        return $lastIndex === null ? null : $value[$lastIndex];
    }

    public function getString(string $name): string
    {
        return (string) $this->getLast($name);
    }

    public function getInt(string $name): int
    {
        $last = $this->getLast($name);

        if (!is_numeric($last)) {
            return 0;
        }

        return (int) $last;
    }

    public function getFloat(string $name): float
    {
        $last = $this->getLast($name);

        if (!is_numeric($last)) {
            return 0.0;
        }

        return (float) $last;
    }

    public function exists(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function getCount(string $name): int
    {
        return count($this->getArray($name));
    }
}
