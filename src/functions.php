<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

use Typhoon\DataStructures\Internal\PerfectHasher;

/**
 * @api
 * @template TObject of object
 * @param class-string<TObject>|array<class-string<TObject>> $classes
 * @param ?non-empty-string $prefix
 * @param callable(TObject): mixed $normalizer
 */
function registerObjectNormalizer(string|array $classes, callable $normalizer, ?string $prefix = null): void
{
    PerfectHasher::global()->registerObjectNormalizer($classes, $normalizer, $prefix);
}
