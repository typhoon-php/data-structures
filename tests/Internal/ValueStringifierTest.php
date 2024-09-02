<?php

declare(strict_types=1);

namespace Typhoon\DataStructures\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Typhoon\DataStructures\SomeEnum;

#[CoversClass(ValueStringifier::class)]
final class ValueStringifierTest extends TestCase
{
    #[TestWith([null, 'null'])]
    #[TestWith([true, 'true'])]
    #[TestWith([false, 'false'])]
    #[TestWith([-1, '-1'])]
    #[TestWith([0, '0'])]
    #[TestWith([0.1123, '0.1123'])]
    #[TestWith([0.001, '0.001'])]
    #[TestWith(['', '""'])]
    #[TestWith(['\'', '"\'"'])]
    #[TestWith(['"', '"\""'])]
    #[TestWith([[], '[]'])]
    #[TestWith([[1], '[1]'])]
    #[TestWith([[1, 2], '[1, 2]'])]
    #[TestWith([['a' => 'b', 'c' => 'd'], '["a" => "b", "c" => "d"]'])]
    #[TestWith([SomeEnum::SomeCase, 'Typhoon\DataStructures\SomeEnum::SomeCase'])]
    #[TestWith([new \stdClass(), 'stdClass{}'])]
    public function test(mixed $value, string $expected): void
    {
        $string = ValueStringifier::stringify($value);

        self::assertSame($expected, $string);
    }

    public function testResource(): void
    {
        $resource = fopen(__FILE__, 'r');

        $string = ValueStringifier::stringify($resource);

        self::assertSame('resource#' . get_resource_id($resource), $string);
    }
}
