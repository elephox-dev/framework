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
class IteratorProviderTest extends TestCase
{
	public function testNormalIterator(): void
	{
		$provider = new IteratorProvider(new ArrayIterator([1, 2, 3]));

		$iterator = $provider->getIterator();
		$iterator2 = $provider->getIterator();

		static::assertInstanceOf(ArrayIterator::class, $iterator);
		static::assertSame($iterator, $iterator2);
		static::assertEquals([1, 2, 3], iterator_to_array($iterator));
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

		static::assertInstanceOf(EagerCachingIterator::class, $iterator);
		static::assertSame($iterator, $iterator2);
		static::assertEquals([1, 2, 3], iterator_to_array($iterator));
	}

	public function testIteratorGenerator(): void
	{
		$provider = new IteratorProvider(static fn () => new ArrayIterator([1, 2, 3]));

		$iterator = $provider->getIterator();
		$iterator2 = $provider->getIterator();

		static::assertInstanceOf(ArrayIterator::class, $iterator);
		static::assertSame($iterator, $iterator2);
		static::assertEquals([1, 2, 3], iterator_to_array($iterator));
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

		static::assertInstanceOf(EagerCachingIterator::class, $iterator);
		static::assertInstanceOf(EagerCachingIterator::class, $iterator2);
		static::assertNotSame($iterator, $iterator2);
		static::assertEquals([1, 2, 3], iterator_to_array($iterator));
		static::assertEquals([1, 2, 3], iterator_to_array($iterator2));
	}
}
