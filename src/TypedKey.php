<?php

declare(strict_types=1);

namespace Typhoon\DataStructures;

/**
 * @api
 * @template V
 * @psalm-consistent-templates
 */
abstract class TypedKey
{
    /**
     * @var array<non-empty-string, self>
     */
    private static array $keys = [];

    /**
     * @internal
     * @psalm-internal Typhoon\DataStructures
     * @param list<non-negative-int> $indexes
     * @return list<self>
     */
    final public static function byIndexes(array $indexes): array
    {
        $keys = array_values(self::$keys);

        return array_map(
            static fn(int $index): self => $keys[$index] ?? throw new \LogicException(),
            $indexes,
        );
    }

    /**
     * @template D
     * @param ?callable(TypedMap): D $default
     * @return static<D>
     */
    final protected static function init(?callable $default = null): static
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];

        \assert(
            isset($trace['class'], $trace['function']) && $trace['class'] === static::class && $trace['function'] !== '',
            \sprintf('Invalid %s call', self::class),
        );

        $name = \sprintf('%s::%s', static::class, $trace['function']);

        \assert(!isset(self::$keys[$name]), \sprintf('Please ensure you memoize key in %s()', $name));

        return self::$keys[$name] = new static(
            index: \count(self::$keys),
            method: $trace['function'],
            default: $default ?? static fn(): never => throw new \LogicException(\sprintf('Key %s() does not have a default value', $name)),
        );
    }

    /**
     * @param non-negative-int $index
     * @param non-empty-string $method
     * @param callable(TypedMap): V $default
     */
    final private function __construct(
        public readonly int $index,
        public readonly string $method,
        private readonly mixed $default,
    ) {}

    /**
     * @return V
     */
    final public function default(TypedMap $map): mixed
    {
        return ($this->default)($map);
    }

    /**
     * @return non-empty-string
     */
    final public function toString(): string
    {
        return self::class . '::' . $this->method . '()';
    }

    final public function __serialize(): never
    {
        throw new \BadMethodCallException(\sprintf('%s does not support serialization', self::class));
    }

    final public function __unserialize(array $_data): never
    {
        throw new \BadMethodCallException(\sprintf('%s does not support deserialization', self::class));
    }

    final public function __clone()
    {
        throw new \BadMethodCallException(\sprintf('%s does not support cloning', self::class));
    }
}
