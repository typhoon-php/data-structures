<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

/**
 * @api
 */
final class MutableTypedMap extends TypedMap
{
    /**
     * @param array<non-negative-int, mixed> $values
     */
    private function __construct(
        private array $values = [],
    ) {}

    public static function create(TypedKVPair ...$kvPairs): self
    {
        $map = new self();

        foreach ($kvPairs as $pair) {
            $map->values[$pair->key->index] = $pair->value;
        }

        return $map;
    }

    public function with(TypedKey $key, mixed $value): static
    {
        $values = $this->values;
        $values[$key->index] = $value;

        return new self($values);
    }

    public function withAll(self $map): static
    {
        return new self(array_replace($this->values, $map->values));
    }

    public function without(TypedKey ...$keys): static
    {
        $values = $this->values;

        foreach ($keys as $key) {
            unset($values[$key->index]);
        }

        return new self($values);
    }

    public function contains(TypedKey $key): bool
    {
        return \array_key_exists($key->index, $this->values);
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset->index, $this->values);
    }

    /**
     * @template V
     * @param TypedKey<V> $key
     * @return V
     */
    public function get(TypedKey $key): mixed
    {
        if (\array_key_exists($key->index, $this->values)) {
            /** @var V */
            return $this->values[$key->index];
        }

        return $key->default($this);
    }

    /**
     * @template V
     * @param TypedKey<V> $offset
     * @return V
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (\array_key_exists($offset->index, $this->values)) {
            /** @var V */
            return $this->values[$offset->index];
        }

        return $offset->default($this);
    }

    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * @template V
     * @param TypedKey<V> $key
     * @param V $value
     */
    public function put(TypedKey $key, mixed $value): void
    {
        $this->values[$key->index] = $value;
    }

    public function putAll(self $map): void
    {
        $this->values = array_replace($this->values, $map->values);
    }

    public function remove(TypedKey ...$keys): void
    {
        foreach ($keys as $key) {
            unset($this->values[$key->index]);
        }
    }

    /**
     * @return list<array{class-string<TypedKey>, non-empty-string, mixed}>
     */
    public function __serialize(): array
    {
        return array_map(
            static fn(TypedKey $key, mixed $value): array => [$key::class, $key->method, $value],
            TypedKey::byIndexes(array_keys($this->values)),
            $this->values,
        );
    }

    /**
     * @param list<array{class-string<TypedKey>, non-empty-string, mixed}> $data
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as [$class, $method, $value]) {
            $key = $class::$method();
            \assert($key instanceof $class);
            $this->values[$key->index] = $value;
        }
    }
}
