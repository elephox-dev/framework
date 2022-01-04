<?php
declare(strict_types=1);

namespace Elephox\PIE;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\PIE\DefaultEqualityComparer
 * @covers \Elephox\PIE\Enumerable
 * @covers \Elephox\PIE\ArrayIterator
 * @covers \Elephox\PIE\WhereIterator
 * @uses   \Elephox\PIE\IsEnumerable
 */
class ReadmeTest extends TestCase
{
	public function testReadme(): void
	{
		$array = [5, 2, 1, 4, 3];
		$pie = Enumerable::from($array);

		$identity = static fn(int $item): int => $item;

		$sum = $pie->sum($identity);
		self::assertEquals(15, $sum);

		$evenSum = $pie->where(fn(int $item) => $item % 2 === 0)->sum($identity);
		self::assertEquals(6, $evenSum);

		$ordered = $pie->orderBy($identity)->toArray();
		self::assertEquals([1, 2, 3, 4, 5], $ordered);
	}
}
