<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

/**
 * @api
 * @implements \ArrayAccess<TypedKey, mixed>
 * @psalm-suppress UnusedClass
 */
abstract class TypedMap implements \ArrayAccess, \Countable
{
    public static function create(TypedKVPair ...$kvPairs): self
    {
        return MutableTypedMap::create(...$kvPairs);
    }

    /**
     * @template V
     * @param TypedKey<V> $key
     * @param V $value
     */
    abstract public function with(TypedKey $key, mixed $value): static;

    abstract public function withAll(MutableTypedMap $map): static;

    abstract public function without(TypedKey ...$keys): static;

    abstract public function contains(TypedKey $key): bool;

    abstract public function offsetExists(mixed $offset): bool;

    /**
     * @template V
     * @param TypedKey<V> $key
     * @return V
     */
    abstract public function get(TypedKey $key): mixed;

    /**
     * @template V
     * @param TypedKey<V> $offset
     * @return V
     */
    abstract public function offsetGet(mixed $offset): mixed;

    /**
     * @return non-negative-int
     */
    abstract public function count(): int;

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \BadMethodCallException();
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \BadMethodCallException();
    }
}
