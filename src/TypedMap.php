<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

use Typhoon\DataStructures\Internal\ArrayTypedMap;

/**
 * @api
 * @implements \ArrayAccess<TypedKey, mixed>
 * @psalm-suppress UnusedClass
 */
abstract class TypedMap implements \ArrayAccess, \Countable
{
    public static function create(): static
    {
        /** @var static */
        return new ArrayTypedMap();
    }

    /**
     * @template V
     * @param TypedKey<V> $key
     * @param V $value
     */
    abstract public function with(TypedKey $key, mixed $value): static;

    abstract public function withAll(self $map): static;

    abstract public function without(TypedKey ...$keys): static;

    abstract public function contains(TypedKey $key): bool;

    final public function offsetExists(mixed $offset): bool
    {
        return $this->contains($offset);
    }

    /**
     * @template V
     * @template D
     * @param TypedKey<V> $key
     * @param D $default
     * @return V|D
     */
    final public function get(TypedKey $key, mixed $default = null): mixed
    {
        return $this->getOr($key, static fn(): mixed => $default);
    }

    /**
     * @template V
     * @template D
     * @param TypedKey<V> $key
     * @param callable(): D $or
     * @return V|D
     */
    abstract public function getOr(TypedKey $key, callable $or): mixed;

    /**
     * @template V
     * @param TypedKey<V> $offset
     * @return V
     * @throws KeyIsNotDefined
     */
    final public function offsetGet(mixed $offset): mixed
    {
        return $this->getOr($offset, static function () use ($offset): void { throw new KeyIsNotDefined($offset); });
    }

    /**
     * @return non-negative-int
     */
    abstract public function count(): int;

    /**
     * @return \Traversable<TypedKey, mixed>
     */
    abstract protected function all(): \Traversable;

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \BadMethodCallException();
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \BadMethodCallException();
    }
}
