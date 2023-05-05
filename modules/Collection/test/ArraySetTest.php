<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @internal
 */
final class ArraySetTest extends TestCase
{
	public function testConstructor(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		self::assertSame(['a', 'b', 'c'], $set->toArray());
	}

	public function testAdd(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);
		$set->add('d');

		self::assertSame(['a', 'b', 'c', 'd'], $set->toArray());

		$set->add('b');
		self::assertSame(['a', 'b', 'c', 'd'], $set->toArray());
	}

	public function testAddAll(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);
		$set->addAll(['d', 'e']);

		self::assertSame(['a', 'b', 'c', 'd', 'e'], $set->toArray());

		$set->addAll(['b', 'a', 'f']);
		self::assertSame(['a', 'b', 'c', 'd', 'e', 'f'], $set->toArray());
	}

	public function testRemove(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		$removed = $set->remove('b');
		self::assertTrue($removed);
		self::assertSame(['a', 'c'], $set->toArray());

		$removed2 = $set->remove('b');
		self::assertFalse($removed2);
		self::assertSame(['a', 'c'], $set->toArray());
	}

	public function testRemoveBy(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		$removed = $set->removeBy(static fn ($item) => $item >= 'b');

		self::assertTrue($removed);
		self::assertSame(['a'], $set->toArray());
	}

	public function testRemoveAll(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		$removed = $set->removeAll();

		self::assertTrue($removed);
		self::assertSame([], $set->toArray());
	}

	public function testCount(): void
	{
		$set = new ArraySet(['a', 'b', 'c']);

		self::assertCount(3, $set);
		self::assertSame(3, $set->count());
		self::assertSame(1, $set->count(fn ($v) => $v === 'b'));
	}
}
