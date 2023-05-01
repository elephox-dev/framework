<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\EagerCachingIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 *
 * @internal
 */
final class IteratorProviderTest extends TestCase
{
	public function testNormalIterator(): void
	{
		$provider = new IteratorProvider(new ArrayIterator([1, 2, 3]));

		$iterator = $provider->getIterator();
		$iterator2 = $provider->getIterator();

		self::assertInstanceOf(ArrayIterator::class, $iterator);
		self::assertSame($iterator, $iterator2);
		self::assertSame([1, 2, 3], iterator_to_array($iterator));
	}

	public function testGenerator(): void
	{
		$generator = (static function () {
			yield 1;
			yield 2;
			yield 3;
		})();
		$provider = new IteratorProvider($generator);

		$iterator = $provider->getIterator();
		$iterator2 = $provider->getIterator();

		self::assertInstanceOf(EagerCachingIterator::class, $iterator);
		self::assertSame($iterator, $iterator2);
		self::assertSame([1, 2, 3], iterator_to_array($iterator));
	}

	public function testIteratorGenerator(): void
	{
		$provider = new IteratorProvider(static fn () => new ArrayIterator([1, 2, 3]));

		$iterator = $provider->getIterator();
		$iterator2 = $provider->getIterator();

		self::assertInstanceOf(ArrayIterator::class, $iterator);
		self::assertSame($iterator, $iterator2);
		self::assertSame([1, 2, 3], iterator_to_array($iterator));
	}

	public function testGeneratorGenerator(): void
	{
		$provider = new IteratorProvider(static function () {
			yield 1;
			yield 2;
			yield 3;
		});

		$iterator = $provider->getIterator();
		$iterator2 = $provider->getIterator();

		self::assertInstanceOf(EagerCachingIterator::class, $iterator);
		self::assertInstanceOf(EagerCachingIterator::class, $iterator2);
		self::assertNotSame($iterator, $iterator2);
		self::assertSame([1, 2, 3], iterator_to_array($iterator));
		self::assertSame([1, 2, 3], iterator_to_array($iterator2));
	}
}
