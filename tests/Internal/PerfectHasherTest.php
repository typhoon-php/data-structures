<?php

declare(strict_types=1);

namespace Typhoon\DataStructures\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Typhoon\DataStructures\KVPair;

#[CoversClass(PerfectHasher::class)]
final class PerfectHasherTest extends TestCase
{
    #[TestWith([null, 'n'])]
    #[TestWith([true, 't'])]
    #[TestWith([false, 'f'])]
    #[TestWith([0, 0])]
    #[TestWith([1, 1])]
    #[TestWith([-100, -100])]
    #[TestWith([0.5, '0.5'])]
    #[TestWith([-0.5, '-0.5'])]
    #[TestWith([NAN, 'NAN'])]
    #[TestWith([INF, 'INF'])]
    #[TestWith(['', '``'])]
    #[TestWith(['test', '`test`'])]
    #[TestWith(['`', '`\``'])]
    #[TestWith(['\`', '`\\\``'])]
    #[TestWith(['```', '`\`\`\``'])]
    #[TestWith([[], '[]'])]
    #[TestWith([[1, 2, 3], '[1,2,3,]'])]
    #[TestWith([['a' => 'b'], '[`a`:`b`,]'])]
    public function testSimpleValues(mixed $value, int|string $expected): void
    {
        $hasher = new PerfectHasher();
        $hash = $hasher->hash($value);

        self::assertSame($expected, $hash);
    }

    /**
     * @param resource $resource
     */
    #[TestWith([STDIN])]
    #[TestWith([STDOUT])]
    #[TestWith([STDERR])]
    public function testResource(mixed $resource): void
    {
        $hasher = new PerfectHasher();
        $hash = $hasher->hash($resource);

        self::assertSame('r' . get_resource_id($resource), $hash);
    }

    public function testObject(): void
    {
        $hasher = new PerfectHasher();
        $hash = $hasher->hash($this);

        self::assertSame('#' . spl_object_id($this), $hash);
    }

    public function testObjectWithCustomEncoder(): void
    {
        $hasher = new PerfectHasher();
        $hasher->registerObjectNormalizer(\Throwable::class, static fn(\Throwable $exception): string => $exception->getMessage());
        $hasher->registerObjectNormalizer(\RuntimeException::class, static fn(\RuntimeException $exception): string => $exception->getMessage());
        $hasher->registerObjectNormalizer([\RangeException::class, \UnexpectedValueException::class], static fn(\RangeException|\UnexpectedValueException $exception): string => $exception->getMessage());

        self::assertSame('Throwable@`logic`', $hasher->hash(new \LogicException('logic')));
        self::assertSame('RuntimeException@`runtime`', $hasher->hash(new \RuntimeException('runtime')));
        self::assertSame('RuntimeException@`overflow`', $hasher->hash(new \OverflowException('overflow')));
        self::assertSame('RangeException|UnexpectedValueException@`range`', $hasher->hash(new \RangeException('range')));
        self::assertSame('RangeException|UnexpectedValueException@`unexpected_value`', $hasher->hash(new \UnexpectedValueException('unexpected_value')));
    }

    /**
     * @param non-empty-string $prefix
     */
    #[TestWith(['abc'])]
    #[TestWith([self::class])]
    #[TestWith(['123'])]
    #[TestWith(['a.b.c'])]
    public function testRegisterObjectEncoderAcceptsValidPrefix(string $prefix): void
    {
        $hasher = new PerfectHasher();

        $hasher->registerObjectNormalizer(self::class, static fn(): bool => true, $prefix);

        self::expectNotToPerformAssertions();
    }

    /**
     * @param non-empty-string $prefix
     */
    #[TestWith(['['])]
    #[TestWith([']'])]
    #[TestWith(['#'])]
    #[TestWith(['@'])]
    #[TestWith([','])]
    public function testRegisterObjectEncoderThrowsOnInvalidPrefix(string $prefix): void
    {
        $hasher = new PerfectHasher();

        $this->expectExceptionObject(new \InvalidArgumentException(\sprintf('Invalid prefix "%s"', $prefix)));

        $hasher->registerObjectNormalizer(self::class, static fn(): bool => true, $prefix);
    }

    public function testRegisterObjectEncoderThrowsAfterEncoding(): void
    {
        $hasher = new PerfectHasher();
        $hasher->hash(1);

        $this->expectExceptionObject(new \LogicException('Please register object hashers before using data structures'));

        $hasher->registerObjectNormalizer(self::class, static fn(): bool => true);
    }

    public function testGlobalReturnsSameInstance(): void
    {
        $hasher = PerfectHasher::global();
        $hasher2 = PerfectHasher::global();

        self::assertSame($hasher, $hasher2);
    }

    public function testGlobalHashesStdClass(): void
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $hash = PerfectHasher::global()->hash($object);

        self::assertSame('stdClass@[`foo`:`bar`,]', $hash);
    }

    public function testGlobalHashesKVPair(): void
    {
        $kv = new KVPair('key', 'value');

        $hash = PerfectHasher::global()->hash($kv);

        self::assertSame('Typhoon\DataStructures\KVPair@[`key`,`value`,]', $hash);
    }

    #[TestWith([new \DateTimeImmutable('2020-04-06 01:02:03.671881 UTC'), 'DateTimeInterface@`20200406010203671881UTC`'])]
    #[TestWith([new \DateTimeImmutable('2020-04-06 01:02:03.671881 Europe/Moscow'), 'DateTimeInterface@`20200406010203671881Europe/Moscow`'])]
    #[TestWith([new \DateTimeImmutable('2020-04-06 01:02:03.671881 +3:30'), 'DateTimeInterface@`20200406010203671881+03:30`'])]
    public function testGlobalHashesDateTime(\DateTimeInterface $date, string $expected): void
    {
        $hash = PerfectHasher::global()->hash($date);

        self::assertSame($expected, $hash);
    }
}
