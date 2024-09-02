<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(KeyIsNotDefined::class)]
final class KeyIsNotDefinedTest extends TestCase
{
    public function testMessage(): void
    {
        $exception = new KeyIsNotDefined(null);

        self::assertSame('Key null is not defined', $exception->getMessage());
    }
}
