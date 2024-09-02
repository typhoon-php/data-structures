<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

use Typhoon\DataStructures\Internal\ValueStringifier;

/**
 * @api
 */
final class KeyIsNotDefined extends \RuntimeException
{
    public function __construct(mixed $key)
    {
        parent::__construct(\sprintf('Key %s is not defined', ValueStringifier::stringify($key)));
    }
}
