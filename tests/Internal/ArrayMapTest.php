<?php

declare(strict_types=1);

namespace Typhoon\DataStructures\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use Typhoon\DataStructures\MapTestCase;
use Typhoon\DataStructures\MutableMap;

#[CoversClass(ArrayMap::class)]
final class ArrayMapTest extends MapTestCase
{
    protected static function createMap(iterable|\Closure $values = []): MutableMap
    {
        $map = new ArrayMap();
        $map->putAll($values);

        return $map;
    }
}
