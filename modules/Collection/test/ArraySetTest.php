<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 *
 * @internal
 */
class ArraySetTest extends TestCase
{
	public function testConstructor(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		static::assertEquals(['a', 'b', 'c'], $set->toArray());
	}

	public function testAdd(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);
		$set->add('d');

		static::assertEquals(['a', 'b', 'c', 'd'], $set->toArray());

		$set->add('b');
		static::assertEquals(['a', 'b', 'c', 'd'], $set->toArray());
	}

	public function testRemove(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		$removed = $set->remove('b');
		static::assertTrue($removed);
		static::assertEquals(['a', 'c'], $set->toArray());

		$removed2 = $set->remove('b');
		static::assertFalse($removed2);
		static::assertEquals(['a', 'c'], $set->toArray());
	}

	public function testRemoveBy(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		$removed = $set->removeBy(static fn ($item) => $item >= 'b');

		static::assertTrue($removed);
		static::assertEquals(['a'], $set->toArray());
	}
}
