<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\OrderedIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\OrderedIterator
 * @covers \Elephox\Collection\DefaultEqualityComparer
 */
class OrderedIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$iterator = new OrderedIterator(new ArrayIterator([4, 2, 1, 5, 2, 3]), fn ($x) => $x, DefaultEqualityComparer::compare(...));

		self::assertFalse($iterator->valid());
		self::assertFalse($iterator->current());
		self::assertNull($iterator->key());

		$iterator->rewind();

		self::assertTrue($iterator->valid());
		self::assertEquals(1, $iterator->current());
		self::assertEquals(0, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertEquals(2, $iterator->current());
		self::assertEquals(1, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertEquals(2, $iterator->current());
		self::assertEquals(2, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertEquals(3, $iterator->current());
		self::assertEquals(3, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertEquals(4, $iterator->current());
		self::assertEquals(4, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertEquals(5, $iterator->current());
		self::assertEquals(5, $iterator->key());

		$iterator->next();

		self::assertFalse($iterator->valid());
		self::assertFalse($iterator->current());
		self::assertNull($iterator->key());
	}
}
