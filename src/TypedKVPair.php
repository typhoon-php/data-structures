<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

/**
 * @api
 * @template V
 */
final class TypedKVPair
{
    /**
     * @param TypedKey<V> $key
     * @param V $value
     */
    public function __construct(
        public readonly TypedKey $key,
        public readonly mixed $value,
    ) {}
}
