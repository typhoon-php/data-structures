<?php

declare(strict_types=1);

namespace Typhoon\DataStructures\Internal;

use Typhoon\DataStructures\MutableTypedMap;
use Typhoon\DataStructures\TypedKey;
use Typhoon\DataStructures\TypedMap;

/**
 * @internal
 * @psalm-internal Typhoon\DataStructures
 */
final class ArrayTypedMap extends MutableTypedMap
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private array $values = [];

    /**
     * @return non-empty-string
     */
    private static function keyToString(TypedKey $key): string
    {
        return $key::class . '::' . $key->name;
    }

    protected function doPut(TypedKey $key, mixed $value): void
    {
        $this->values[self::keyToString($key)] = $value;
    }

    public function putAll(TypedMap $map): void
    {
        if ($map instanceof self) {
            $this->values = [...$this->values, ...$map->values];
        } else {
            parent::putAll($map);
        }
    }

    public function contains(TypedKey $key): bool
    {
        return isset($this->values[self::keyToString($key)]);
    }

    /**
     * @template V
     * @template D
     * @param TypedKey<V> $key
     * @param callable(): D $or
     * @return V|D
     */
    public function getOr(TypedKey $key, callable $or): mixed
    {
        $keyString = self::keyToString($key);

        if (\array_key_exists($keyString, $this->values)) {
            /** @var V */
            return $this->values[$keyString];
        }

        return $or();
    }

    public function remove(TypedKey ...$keys): void
    {
        foreach ($keys as $key) {
            unset($this->values[self::keyToString($key)]);
        }
    }

    public function count(): int
    {
        return \count($this->values);
    }

    protected function all(): \Traversable
    {
        foreach ($this->values as $keyString => $value) {
            /** @var TypedKey $key */
            $key = \constant($keyString);
            yield $key => $value;
        }
    }
}
