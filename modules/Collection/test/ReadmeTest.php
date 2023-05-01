<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\Iterator\OrderedIterator
 * @covers \Elephox\Collection\OrderedEnumerable
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses   \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class ReadmeTest extends TestCase
{
	public function testReadme(): void
	{
		$array = [5, 2, 1, 4, 3];
		$pie = KeyedEnumerable::from($array);

		$identity = static fn (int $item): int => $item;

		$sum = $pie->sum($identity);
		self::assertSame(15, $sum);

		$evenSum = $pie->where(static fn (int $item) => $item % 2 === 0)->sum($identity);
		self::assertSame(6, $evenSum);

		$ordered = $pie->orderBy($identity)->toArray();
		self::assertSame([1, 2, 3, 4, 5], $ordered);
	}
}
