<?php
declare(strict_types=1);

namespace Elephox\Collection;

use AssertionError;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Collection\ObjectSet
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\Iterator\SplObjectStorageIterator
 * @covers \Elephox\Collection\Iterator\FlipIterator
 *
 * @internal
 */
final class ObjectSetTest extends TestCase
{
	public function testAdd(): void
	{
		$set = new ObjectSet();
		$obj = new stdClass();

		self::assertTrue($set->add($obj));
		self::assertCount(1, $set);

		self::assertFalse($set->add($obj));
		self::assertCount(1, $set);
	}

	public function testAddAll(): void
	{
		$set = new ObjectSet();
		$obj = new stdClass();
		$obj2 = new stdClass();
		$obj3 = new stdClass();

		self::assertTrue($set->addAll([$obj, $obj2]));
		self::assertCount(2, $set);

		self::assertFalse($set->addAll([$obj2, $obj3]));
		self::assertCount(3, $set);
	}

	public function testAddInvalid(): void
	{
		$this->expectException(AssertionError::class);
		$this->expectExceptionMessage('Argument 1 passed to Elephox\Collection\ObjectSet::add() must be an object, null given');

		$set = new ObjectSet();
		$set->add(null);
	}

	public function testRemove(): void
	{
		$ref = new stdClass();
		$set = new ObjectSet();
		$set->add($ref);

		self::assertfalse($set->remove(new stdClass()));
		self::assertTrue($set->remove($ref));
	}

	public function testInvalidRemove(): void
	{
		$this->expectException(AssertionError::class);
		$this->expectExceptionMessage('Argument 1 passed to Elephox\Collection\ObjectSet::remove() must be an object, null given');

		$set = new ObjectSet();
		$set->remove(null);
	}

	public function testRemoveBy(): void
	{
		$ref1 = new stdClass();
		$ref1->test = true;

		$ref2 = new stdClass();
		$ref2->test = true;

		$ref3 = new stdClass();
		$ref3->test = false;

		$ref4 = new stdClass();
		$ref4->test = false;

		$set = new ObjectSet();
		$set->add($ref1);
		$set->add($ref2);
		$set->add($ref3);
		$set->add($ref4);

		self::assertFalse($set->removeBy(static fn ($x) => false));
		self::assertTrue($set->removeBy(static fn ($x) => $x->test));
		self::assertCount(2, $set);
	}
}
