<?php

declare(strict_types=1);

namespace Ordinary\Command\Argument\Option;

use Generator;
use Ordinary\Command\UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Throwable;

class OptionRepositoryTest extends TestCase
{
    public static function createProvider(): Generator
    {
        yield [['foo'], null];
        yield [[false], null];
        yield [[1], UnexpectedValueException::class];
        yield [[1.1], UnexpectedValueException::class];
        yield [[[[]]], UnexpectedValueException::class];
        yield [[null], UnexpectedValueException::class];
        yield [[true], UnexpectedValueException::class];
    }

    public static function gettingFormattedValuesProvider(): Generator
    {
        yield [[], [], '', 0, 0.0, null, null, 0];

        yield ['0', ['0'], '0', 0, 0.0, '0', '0', 1];
        yield ['1', ['1'], '1', 1, 1.0, '1', '1', 1];
        yield ['-1', ['-1'], '-1', -1, -1.0, '-1', '-1', 1];
        yield ['10', ['10'], '10', 10, 10.0, '10', '10', 1];
        yield ['5.5', ['5.5'], '5.5', 5, 5.5, '5.5', '5.5', 1];

        yield ['foo', ['foo'], 'foo', 0, 0.0, 'foo', 'foo', 1];

        yield [['0', '1'], ['0', '1'], '1', 1, 1.0, '0', '1', 2];
        yield [['1', '0'], ['1', '0'], '0', 0, 0.0, '1', '0', 2];

        yield [false, [false], '', 0, 0.0, false, false, 1];

        yield [['2', '1', '3'], ['2', '1', '3'], '3', 3, 3.0, '2', '3', 3];
    }

    /**
     * @param array<string|int, mixed> $payload
     * @param ?class-string<Throwable> $exceptionClass
     * @dataProvider createProvider
     */
    public function testCreate(array $payload, ?string $exceptionClass): void
    {
        if ($exceptionClass) {
            self::expectException($exceptionClass);
        }

        $repo = OptionRepository::create($payload);

        self::assertInstanceOf(OptionRepository::class, $repo);
    }

    /**
     * @param string|false|mixed[] $value
     * @param mixed[] $arrayValue
     * @dataProvider gettingFormattedValuesProvider
     */
    public function testGettingFormattedValues(
        string|false|array $value,
        array $arrayValue,
        string $stringValue,
        int $intValue,
        float $floatValue,
        string|false|array|null $first,
        string|false|array|null $last,
        int $count,
    ): void {
        $test = 'foo';
        $repo = OptionRepository::create($options = [$test => $value, 'bar' => '0']);

        self::assertTrue($repo->exists($test));

        self::assertSame($value, $repo->get($test));
        self::assertSame($arrayValue, $repo->getArray($test));
        self::assertSame($stringValue, $repo->getString($test));
        self::assertSame($intValue, $repo->getInt($test));
        self::assertSame($floatValue, $repo->getFloat($test));

        self::assertSame($first, $repo->getFirst($test));
        self::assertSame($last, $repo->getLast($test));

        self::assertSame($count, $repo->getCount($test));

        self::assertSame($options, $repo->all());
    }

    public function testNonExistentOption(): void
    {
        $test = 'foo';
        $repo = OptionRepository::create($options = ['bar' => 'bar']);

        self::assertFalse($repo->exists($test));

        self::assertNull($repo->get($test));
        self::assertNull($repo->getFirst($test));
        self::assertNull($repo->getLast($test));

        self::assertSame([], $repo->getArray($test));
        self::assertSame('', $repo->getString($test));
        self::assertSame(0, $repo->getInt($test));
        self::assertSame(0.0, $repo->getFloat($test));
        self::assertSame(0, $repo->getCount($test));
    }
}
