<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

/**
 * @api
 */
abstract class MutableTypedMap extends TypedMap
{
    /**
     * @template V
     * @param TypedKey<V> $key
     * @param V $value
     */
    final public function with(TypedKey $key, mixed $value): static
    {
        if ($key instanceof OptionalTypedKey && $value === $key->default($this)) {
            if ($this->contains($key)) {
                $copy = clone $this;
                $copy->remove($key);

                return $copy;
            }

            return $this;
        }

        $copy = clone $this;
        $copy->doPut($key, $value);

        return $copy;
    }

    final public function withAll(TypedMap $map): static
    {
        $copy = clone $this;
        foreach ($map->all() as $key => $value) {
            $copy->put($key, $value);
        }

        return $copy;
    }

    final public function without(TypedKey ...$keys): static
    {
        if ($keys === []) {
            return $this;
        }

        $copy = clone $this;
        $copy->remove(...$keys);

        return $copy;
    }

    /**
     * @template V
     * @param TypedKey<V> $key
     * @param V $value
     */
    final public function put(TypedKey $key, mixed $value): void
    {
        if ($key instanceof OptionalTypedKey && $value === $key->default($this)) {
            $this->remove($key);
        } else {
            $this->doPut($key, $value);
        }
    }

    /**
     * @template V
     * @param TypedKey<V> $key
     * @param V $value
     */
    abstract protected function doPut(TypedKey $key, mixed $value): void;

    public function putAll(TypedMap $map): void
    {
        foreach ($map->all() as $key => $value) {
            $this->put($key, $value);
        }
    }

    abstract public function remove(TypedKey ...$keys): void;
}
