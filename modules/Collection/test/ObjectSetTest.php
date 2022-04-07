<?php
declare(strict_types=1);

namespace Elephox\Collection;

use InvalidArgumentException;
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
class ObjectSetTest extends TestCase
{
	public function testAdd(): void
	{
		$set = new ObjectSet();
		static::assertTrue($set->add(new stdClass()));
		static::assertCount(1, $set);
	}

	public function testAddInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$set = new ObjectSet();
		$set->add(null);
	}

	public function testRemove(): void
	{
		$ref = new stdClass();
		$set = new ObjectSet();
		$set->add($ref);

		self::assertfalse($set->remove(new stdClass()));
		static::assertTrue($set->remove($ref));
	}

	public function testInvalidRemove(): void
	{
		$this->expectException(InvalidArgumentException::class);

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

		static::assertFalse($set->removeBy(static fn ($x) => false));
		static::assertTrue($set->removeBy(static fn ($x) => $x->test));
		static::assertCount(2, $set);
	}
}
