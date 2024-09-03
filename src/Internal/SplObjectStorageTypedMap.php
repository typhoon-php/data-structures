<?php

declare(strict_types=1);

namespace Typhoon\DataStructures\Internal;

use Typhoon\DataStructures\MutableTypedMap;
use Typhoon\DataStructures\TypedKey;
use Typhoon\DataStructures\TypedMap;

/**
 * @internal
 * @psalm-internal Typhoon\DataStructures
 * @psalm-suppress UnusedClass
 */
final class SplObjectStorageTypedMap extends MutableTypedMap
{
    /**
     * @var \SplObjectStorage<TypedKey, mixed>
     */
    private \SplObjectStorage $values;

    public function __construct()
    {
        /** @var \SplObjectStorage<TypedKey, mixed> */
        $this->values = new \SplObjectStorage();
    }

    protected function doPut(TypedKey $key, mixed $value): void
    {
        $this->values->attach($key, $value);
    }

    public function contains(TypedKey $key): bool
    {
        return $this->values->contains($key);
    }

    /**
     * @template V
     * @template D
     * @param TypedKey<V> $key
     * @param callable(): D $or
     * @return V|D
     */
    public function getOr(mixed $key, callable $or): mixed
    {
        if ($this->values->contains($key)) {
            /** @var V */
            return $this->values->offsetGet($key);
        }

        return $or();
    }

    public function remove(TypedKey ...$keys): void
    {
        foreach ($keys as $key) {
            $this->values->detach($key);
        }
    }

    public function putAll(TypedMap $map): void
    {
        if ($map instanceof self) {
            $this->values->addAll($map->values);
        } else {
            parent::putAll($map);
        }
    }

    public function count(): int
    {
        return \count($this->values);
    }

    protected function all(): \Traversable
    {
        foreach ($this->values as $key) {
            yield $key => $this->values->getInfo();
        }
    }
}
