<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

use Typhoon\DataStructures\Internal\UniqueHasher;

/**
 * @api
 * @template TObject of object
 * @param class-string<TObject>|array<class-string<TObject>> $classes
 * @param ?non-empty-string $prefix
 * @param callable(TObject): mixed $hasher
 */
function registerObjectHasher(string|array $classes, callable $hasher, ?string $prefix = null): void
{
    UniqueHasher::global()->registerObjectHasher($classes, $hasher, $prefix);
}
