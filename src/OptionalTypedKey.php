<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

/**
 * @api
 * @template-covariant TValue
 * @extends TypedKey<TValue>
 */
interface OptionalTypedKey extends TypedKey
{
    /**
     * @return TValue
     */
    public function default(TypedMap $map): mixed;
}
