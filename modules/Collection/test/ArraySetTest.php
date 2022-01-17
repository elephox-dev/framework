<?php

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 */
class ArraySetTest extends TestCase
{
	public function testConstructor(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		self::assertEquals(['a', 'b', 'c'], $set->toArray());
	}

	public function testAdd(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);
		$set->add('d');

		self::assertEquals(['a', 'b', 'c', 'd'], $set->toArray());

		$set->add('b');
		self::assertEquals(['a', 'b', 'c', 'd'], $set->toArray());
	}

	public function testRemove(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		$removed = $set->remove('b');
		self::assertTrue($removed);
		self::assertEquals(['a', 'c'], $set->toArray());

		$removed2 = $set->remove('b');
		self::assertFalse($removed2);
		self::assertEquals(['a', 'c'], $set->toArray());
	}

	public function testRemoveBy(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		$removed = $set->removeBy(function($item) {
			return $item >= 'b';
		});

		self::assertTrue($removed);
		self::assertEquals(['a'], $set->toArray());
	}
}
