<?php
declare(strict_types=1);

namespace Elephox\PIE;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\PIE\Enumerable
 * @covers \Elephox\PIE\RangeIterator
 * @covers \Elephox\PIE\SelectIterator
 * @covers \Elephox\PIE\WhileIterator
 * @uses \Elephox\PIE\IsEnumerable
 */
class EnumerableTest extends TestCase
{
	public function testSelect(): void
	{
		self::assertEquals(
			[-2, -6, -10],
			Enumerable::range(-1, -5, -2)
				->select(fn(int $x): int => $x * 2)
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
