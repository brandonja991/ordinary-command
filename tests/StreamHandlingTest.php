<?php

declare(strict_types=1);

namespace Ordinary\Command;

use Generator;
use PHPUnit\Framework\TestCase;

class StreamHandlingTest extends TestCase
{
    public static function withStreamsProvider(): Generator
    {
        [$stdin, $stdout, $stderr] = [
            fopen('php://temp', 'w+'),
            fopen('php://temp', 'w+'),
            fopen('php://temp', 'w+'),
        ];

        yield [null, null, null];
        yield [$stdin, null, null];
        yield [null, $stdout, null];
        yield [null, null, $stderr];
        yield [$stdin, $stdout, $stderr];
    }

    /** @dataProvider withStreamsProvider */
    public function testWithStreamsResetTrue(mixed $in, mixed $out, mixed $err): void
    {
        $obj = $this->makeStreamHandling();

        self::assertIsResource($obj->getIn());
        self::assertIsResource($obj->getOut());
        self::assertIsResource($obj->getErr());

        $a = $obj->withStreams($in, $out, $err);

        self::assertIsResource($a->getIn());
        self::assertIsResource($a->getOut());
        self::assertIsResource($a->getErr());

        self::assertNotSame($obj->getIn(), $a->getIn());
        self::assertNotSame($obj->getOut(), $a->getOut());
        self::assertNotSame($obj->getErr(), $a->getErr());

        if ($in) {
            self::assertSame($in, $a->getIn());
        }

        if ($out) {
            self::assertSame($out, $a->getOut());
        }

        if ($err) {
            self::assertSame($err, $a->getErr());
        }
    }

    /** @dataProvider withStreamsProvider */
    public function testWithStreamsResetFalse(mixed $in, mixed $out, mixed $err): void
    {
        $obj = $this->makeStreamHandling();

        self::assertIsResource($obj->getIn());
        self::assertIsResource($obj->getOut());
        self::assertIsResource($obj->getErr());

        $a = $obj->withStreams($in, $out, $err, false);

        self::assertSame($in ?: $obj->getIn(), $a->getIn());
        self::assertSame($out ?: $obj->getOut(), $a->getOut());
        self::assertSame($err ?: $obj->getErr(), $a->getErr());

        if ($in) {
            self::assertNotSame($obj->getIn(), $a->getIn());
        }

        if ($out) {
            self::assertNotSame($obj->getOut(), $a->getOut());
        }

        if ($err) {
            self::assertNotSame($obj->getErr(), $a->getErr());
        }
    }

    private function makeStreamHandling(): object
    {
        return new class () {
            use StreamHandling;

            public function getIn(): mixed
            {
                return $this->stdin();
            }

            public function getOut(): mixed
            {
                return $this->stdout();
            }

            public function getErr(): mixed
            {
                return $this->stderr();
            }
        };
    }
}
