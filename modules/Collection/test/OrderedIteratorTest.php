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
final class OrderedIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$iterator = new OrderedIterator(new ArrayIterator([4, 2, 1, 5, 2, 3]), static fn ($x) => $x, DefaultEqualityComparer::compare(...));

		self::assertFalse($iterator->valid());
		self::assertFalse($iterator->current());
		self::assertNull($iterator->key());

		$iterator->rewind();

		self::assertTrue($iterator->valid());
		self::assertSame(1, $iterator->current());
		self::assertSame(0, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertSame(2, $iterator->current());
		self::assertSame(1, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertSame(2, $iterator->current());
		self::assertSame(2, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertSame(3, $iterator->current());
		self::assertSame(3, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertSame(4, $iterator->current());
		self::assertSame(4, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertSame(5, $iterator->current());
		self::assertSame(5, $iterator->key());

		$iterator->next();

		self::assertFalse($iterator->valid());
		self::assertFalse($iterator->current());
		self::assertNull($iterator->key());
	}

	public function testIntegerKeyReordering(): void
	{
		$iterator = new OrderedIterator(new ArrayIterator(['a' => 5, 1 => 4, 'b' => 2, 'c' => 3, 2 => 1, 3 => 0]), static fn ($x) => $x, DefaultEqualityComparer::compare(...));

		$iterator->rewind();
		self::assertTrue($iterator->valid());

		self::assertSame(0, $iterator->current());
		self::assertSame(0, $iterator->key());

		$iterator->next();

		self::assertSame(1, $iterator->current());
		self::assertSame(1, $iterator->key());

		$iterator->next();

		self::assertSame(2, $iterator->current());
		self::assertSame('b', $iterator->key());

		$iterator->next();

		self::assertSame(3, $iterator->current());
		self::assertSame('c', $iterator->key());

		$iterator->next();

		self::assertSame(4, $iterator->current());
		self::assertSame(4, $iterator->key());

		$iterator->next();

		self::assertSame(5, $iterator->current());
		self::assertSame('a', $iterator->key());
	}
}
