<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

/**
 * @psalm-suppress PossiblyUnusedProperty
 */
final class SomeClass
{
    public function __construct(
        public readonly mixed $data,
    ) {}
}
