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
		static::assertSame(1, $iterator->current());
		static::assertSame(0, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertSame(2, $iterator->current());
		static::assertSame(1, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertSame(2, $iterator->current());
		static::assertSame(2, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertSame(3, $iterator->current());
		static::assertSame(3, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertSame(4, $iterator->current());
		static::assertSame(4, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertSame(5, $iterator->current());
		static::assertSame(5, $iterator->key());

		$iterator->next();

		static::assertFalse($iterator->valid());
		static::assertFalse($iterator->current());
		static::assertNull($iterator->key());
	}

	public function testIntegerKeyReordering(): void
	{
		$iterator = new OrderedIterator(new ArrayIterator(['a' => 5, 1 => 4, 'b' => 2, 'c' => 3, 2 => 1, 3 => 0]), static fn ($x) => $x, DefaultEqualityComparer::compare(...));

		$iterator->rewind();
		static::assertTrue($iterator->valid());

		static::assertSame(0, $iterator->current());
		static::assertSame(0, $iterator->key());

		$iterator->next();

		static::assertSame(1, $iterator->current());
		static::assertSame(1, $iterator->key());

		$iterator->next();

		static::assertSame(2, $iterator->current());
		static::assertSame('b', $iterator->key());

		$iterator->next();

		static::assertSame(3, $iterator->current());
		static::assertSame('c', $iterator->key());

		$iterator->next();

		static::assertSame(4, $iterator->current());
		static::assertSame(4, $iterator->key());

		$iterator->next();

		static::assertSame(5, $iterator->current());
		static::assertSame('a', $iterator->key());
	}
}
