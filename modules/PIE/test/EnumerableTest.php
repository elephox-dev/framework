<?php
declare(strict_types=1);

namespace Elephox\PIE;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\PIE\Enumerable
 * @covers \Elephox\PIE\RangeIterator
 * @covers \Elephox\PIE\SelectIterator
 * @covers \Elephox\PIE\KeySelectIterator
 * @covers \Elephox\PIE\WhileIterator
 * @covers \Elephox\PIE\ReverseIterator
 * @covers \Elephox\PIE\DefaultEqualityComparer
 * @uses \Elephox\PIE\IsEnumerable
 */
class EnumerableTest extends TestCase
{
	public function testReverse(): void
	{
		$this->assertEquals(
			[5, 4, 3, 2, 1],
			Enumerable::from([1, 2, 3, 4, 5])->reverse()->toArray()
		);
	}

	public function testSelect(): void
	{
		self::assertEquals(
			[2, 4, 6, 8, 10],
			Enumerable::range(1, 5)
				->select(fn(int $x): int => $x * 2)
				->toList()
		);
	}

	public function testSelectMany(): void
	{
		self::assertEquals(
			[
				1,
				1, 2,
				1, 2, 3,
				1, 2, 3, 4,
				1, 2, 3, 4, 5
			],
			Enumerable::range(1, 5)
				->selectMany(fn(int $x): GenericEnumerable => Enumerable::range(1, $x))
				->toList()
		);
	}

	public function testSequenceEqual(): void
	{
		self::assertTrue(
			Enumerable::range(1, 5)->sequenceEqual(Enumerable::range(1, 5))
		);

		self::assertFalse(
			Enumerable::range(1, 5)->sequenceEqual(Enumerable::range(1, 6))
		);

		self::assertTrue(Enumerable::empty()->sequenceEqual(Enumerable::empty()));
	}

	public function testSingle(): void
	{
		self::assertEquals(
			2,
			Enumerable::from([2])->single()
		);
	}

	public function testSingleMultipleElements(): void
	{
		$this->expectException(InvalidArgumentException::class);
		Enumerable::from([1, 2])->single();
	}

	public function testSingleNoElements(): void
	{
		$this->expectException(InvalidArgumentException::class);
		Enumerable::empty()->single();
	}

	public function testSingleOrDefault(): void
	{
		self::assertEquals(
			1,
			Enumerable::range(1, 5)->singleOrDefault(null, fn(int $x): bool => $x === 1)
		);

		self::assertNull(
			Enumerable::range(1, 5)->singleOrDefault(null, fn(int $x): bool => $x === 6)
		);
	}

	public function testSkip(): void
	{
		self::assertEquals(
			[3, 4, 5],
			Enumerable::range(1, 5)
				->skip(2)
				->toList()
		);
	}

	public function testSkipLast(): void
	{
		self::assertEquals(
			[1, 2, 3],
			Enumerable::range(1, 5)
				->skipLast(2)
				->toList()
		);
	}

	public function testSkipWhile(): void
	{
		self::assertEquals(
			[3, 4, 5],
			Enumerable::range(1,5)
				->skipWhile(fn(int $x): bool => $x < 3)
				->toList()
		);
	}

	public function testTake(): void
	{
		self::assertEquals(
			[0, 1, 2],
			Enumerable::range(0, 6)->take(3)->toList()
		);
	}

	public function testTakeLast(): void
	{
		self::assertEquals(
			[5, 6],
			Enumerable::range(0, 6)->takeLast(2)->toList()
		);
	}

	public function testTakeLastInvalid(): void
	{
		self::assertEquals(
			[],
			Enumerable::range(0, 6)->takeLast(-2)->toList()
		);
	}

	public function testTakeLastEmpty(): void
	{
		self::assertEquals(
			[],
			Enumerable::empty()->takeLast(1)->toList()
		);
	}

	public function testTakeWhile(): void
	{
		self::assertEquals(
			[0, 1, 2],
			Enumerable::range(0, 6)->takeWhile(fn(int $x): bool => $x < 3)->toList()
		);
	}

	public function testZip(): void
	{
		self::assertEquals(
			[
				[1, 4],
				[2, 5],
				[3, 6],
			],
			Enumerable::range(1, 3)->zip(Enumerable::range(4, 6))->toList()
		);
	}
}
