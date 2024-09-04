<?php

declare(strict_types=1);

namespace Typhoon\DataStructures\Internal;

use Typhoon\DataStructures\KVPair;

/**
 * @internal
 * @psalm-internal Typhoon\DataStructures
 */
final class UniqueHasher
{
    private const NULL = 'n';
    private const TRUE = 't';
    private const FALSE = 'f';
    private const STRING_QUOTE = '`';
    private const ARRAY_START = '[';
    private const ARRAY_END = ']';
    private const ARRAY_COMMA = ',';
    private const ARRAY_COLON = ':';
    private const OBJECT_ID_PREFIX = '#';
    private const OBJECT_PREFIX_PATTERN = '/^[\w\\\.|]+$/';
    private const OBJECT_DATA_PREFIX = '@';
    private const RESOURCE_ID_PREFIX = 'r';

    private static ?self $global = null;

    public static function global(): self
    {
        if (self::$global !== null) {
            return self::$global;
        }

        $default = new self();
        $default->registerObjectHasher(\stdClass::class, static fn(\stdClass $object): array => (array) $object);
        $default->registerObjectHasher(\DateTimeInterface::class, static fn(\DateTimeInterface $object): string => $object->format('YmdHisue'));
        $default->registerObjectHasher(KVPair::class, static fn(KVPair $object): array => [$object->key, $object->value]);

        return self::$global = $default;
    }

    private bool $locked = false;

    /**
     * @var \Closure(object): non-empty-string
     */
    private \Closure $defaultObjectHasher;

    /**
     * @var array<non-empty-string, callable(object, self): non-empty-string>
     */
    private array $objectHashers = [];

    /**
     * @var \WeakMap<object, non-empty-string>
     */
    private \WeakMap $objectHashes;

    public function __construct()
    {
        $this->defaultObjectHasher = static fn(object $object): string => self::OBJECT_ID_PREFIX . spl_object_id($object);
        /** @var \WeakMap<object, non-empty-string> */
        $this->objectHashes = new \WeakMap();
    }

    /**
     * @template TObject of object
     * @param class-string<TObject>|array<class-string<TObject>> $classes
     * @param callable(TObject): mixed $hasher
     * @param ?non-empty-string $prefix
     */
    public function registerObjectHasher(string|array $classes, callable $hasher, ?string $prefix = null): void
    {
        if ($this->locked) {
            throw new \LogicException('Please register object hashers before using data structures');
        }

        $classes = (array) $classes;
        $prefix ??= implode('|', $classes);

        if (preg_match(self::OBJECT_PREFIX_PATTERN, $prefix) !== 1) {
            throw new \InvalidArgumentException(\sprintf('Invalid prefix "%s"', $prefix));
        }

        $objectHasher =
            /** @param TObject $object */
            static fn(object $object, self $mainHasher): string => $prefix . self::OBJECT_DATA_PREFIX . $mainHasher->hash($hasher($object));

        foreach ($classes as $class) {
            /** @psalm-suppress InvalidPropertyAssignmentValue */
            $this->objectHashers[$class] = $objectHasher;
        }
    }

    /**
     * @return int|non-empty-string
     */
    public function hash(mixed $value): int|string
    {
        $this->locked = true;

        if (\is_int($value)) {
            return $value;
        }

        if (\is_string($value)) {
            return self::STRING_QUOTE . addcslashes($value, self::STRING_QUOTE) . self::STRING_QUOTE;
        }

        if (\is_object($value)) {
            return $this->objectHashes[$value] ??= $this->hashObject($value);
        }

        if ($value === null) {
            return self::NULL;
        }

        if ($value === true) {
            return self::TRUE;
        }

        if ($value === false) {
            return self::FALSE;
        }

        if (\is_float($value)) {
            return (string) $value;
        }

        if (\is_array($value)) {
            $hash = self::ARRAY_START;

            if (array_is_list($value)) {
                foreach ($value as $item) {
                    $hash .= self::hash($item) . self::ARRAY_COMMA;
                }
            } else {
                foreach ($value as $key => $item) {
                    $hash .= self::hash($key) . self::ARRAY_COLON . self::hash($item) . self::ARRAY_COMMA;
                }
            }

            return $hash . self::ARRAY_END;
        }

        if (\is_resource($value)) {
            return self::RESOURCE_ID_PREFIX . get_resource_id($value);
        }

        throw new \LogicException(\sprintf('Type %s is not supported', get_debug_type($value)));
    }

    /**
     * @return non-empty-string
     */
    private function hashObject(object $object): string
    {
        $class = $object::class;

        if (isset($this->objectHashers[$class])) {
            return $this->objectHashers[$class]($object, $this);
        }

        foreach (class_parents($class) as $parent) {
            if (isset($this->objectHashers[$parent])) {
                return ($this->objectHashers[$class] = $this->objectHashers[$parent])($object, $this);
            }
        }

        foreach (class_implements($class) as $interface) {
            if (isset($this->objectHashers[$interface])) {
                return ($this->objectHashers[$class] = $this->objectHashers[$interface])($object, $this);
            }
        }

        return ($this->objectHashers[$class] = $this->defaultObjectHasher)($object);
    }
}
