<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\OrderedIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\OrderedIterator
 * @covers \Elephox\Collection\DefaultEqualityComparer
 *
 * @internal
 */
class OrderedIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$iterator = new OrderedIterator(new ArrayIterator([4, 2, 1, 5, 2, 3]), static fn ($x) => $x, DefaultEqualityComparer::compare(...));

		static::assertFalse($iterator->valid());
		static::assertFalse($iterator->current());
		static::assertNull($iterator->key());

		$iterator->rewind();

		static::assertTrue($iterator->valid());
		static::assertEquals(1, $iterator->current());
		static::assertEquals(0, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertEquals(2, $iterator->current());
		static::assertEquals(1, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertEquals(2, $iterator->current());
		static::assertEquals(2, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertEquals(3, $iterator->current());
		static::assertEquals(3, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertEquals(4, $iterator->current());
		static::assertEquals(4, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertEquals(5, $iterator->current());
		static::assertEquals(5, $iterator->key());

		$iterator->next();

		static::assertFalse($iterator->valid());
		static::assertFalse($iterator->current());
		static::assertNull($iterator->key());
	}
}
