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
 * @covers \Elephox\PIE\UniqueByIterator
 * @covers \Elephox\PIE\DefaultEqualityComparer
 * @uses \Elephox\PIE\IsEnumerable
 */
class EnumerableTest extends TestCase
{
	public function testJoin(): void
	{
		self::assertEquals(
			[2, 4, 6, 8, 10],
			Enumerable::range(1, 5)->join(
				Enumerable::range(1, 5),
				fn(int $a) => $a,
				fn(int $b) => $b,
				fn(int $a, int $b) => $a + $b
			)->toList()
		);
	}

	public function testLast(): void
	{
		self::assertEquals(
			'c',
			Enumerable::from(['a', 'b', 'c'])->last()
		);
	}

	public function testLastOrDefault(): void
	{
		self::assertEquals(3, Enumerable::from([1, 2, 3])->lastOrDefault(null));
		self::assertNull(Enumerable::empty()->lastOrDefault(null));
	}

	public function testMax(): void
	{
		self::assertEquals(
			10,
			Enumerable::range(1, 10)->max(fn(int $x) => $x)
		);
	}

	public function testMin(): void
	{
		self::assertEquals(
			1,
			Enumerable::range(1, 3)->min(fn(int $x) => $x)
		);
	}

	public function testOrderBy(): void
	{
		self::assertEquals(
			[1, 2, 3, 4, 5, 6],
			Enumerable::from([6, 2, 5, 1, 4, 3])->orderBy(fn(int $x) => $x)->toList()
		);
	}

	public function testOrderByDescending(): void
	{
		self::assertEquals(
			[
				[
					'name' => 'b',
					'age' => 2,
				],
				[
					'name' => 'a',
					'age' => 1,
				],
			],
			Enumerable::from([
				['name' => 'a', 'age' => 1],
				['name' => 'b', 'age' => 2],
			])->orderByDescending(fn($x) => $x['age'])->toList()
		);
	}

	public function testPrepend(): void
	{
		self::assertEquals(
			[5, 1, 2, 3],
			Enumerable::range(1, 3)->prepend(5)->toList()
		);
	}

	public function testReverse(): void
	{
		self::assertEquals(
			[5, 4, 3, 2, 1],
			Enumerable::range(1, 5)->reverse()->toArray()
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

	public function testUnion(): void
	{
		$a = Enumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = Enumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		self::assertEquals(
			[5, 3, 9, 7, 8, 6, 4, 1, 0],
			$a->union($b)->toList()
		);
	}

	public function testUnionBy(): void
	{
		$a = Enumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = Enumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		self::assertEquals(
			[5, 3, 9, 7, 6],
			$a->unionBy($b, fn(int $a) => $a % 5)->toList()
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
