<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\GroupingIterator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Collection\Iterator\GroupingIterator
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Grouping
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\Iterator\RangeIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @internal
 */
final class GroupingIteratorTest extends TestCase
{
	public function testCurrentThrowsIfEmpty(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('No current group key');

		$iterator = new GroupingIterator(new ArrayIterator([]), static fn (int $v): int => $v);
		$iterator->rewind();
		$iterator->current();
	}

	public function testKey(): void
	{
		$groupIterator = new GroupingIterator(Enumerable::range(0, 5), static fn (int $v): int => $v % 2);
		$groupIterator->rewind();

		$firstKey = $groupIterator->key();
		self::assertSame(0, $firstKey);
		$firstGroup = $groupIterator->current();
		self::assertSame($firstKey, $firstGroup->groupKey());
		self::assertSame([0 => 0, 2 => 2, 4 => 4], $firstGroup->toArray());

		$groupIterator->next();
		$secondKey = $groupIterator->key();
		self::assertSame(1, $secondKey);
		$secondGroup = $groupIterator->current();
		self::assertSame($secondKey, $secondGroup->groupKey());
		self::assertSame([1 => 1, 3 => 3, 5 => 5], $secondGroup->toArray());

		$groupIterator->next();
		self::assertFalse($groupIterator->valid());
	}

	public function testNonNumericGroupingKey(): void
	{
		$groupIterator = new GroupingIterator(Enumerable::range(0, 5), static fn (int $v): string => (string) ($v % 2));

		$groupIterator->rewind();
		self::assertSame('0', $groupIterator->current()->groupKey());

		$groupIterator->next();
		self::assertSame('1', $groupIterator->current()->groupKey());

		$groupIterator->next();
		self::assertFalse($groupIterator->valid());
	}
}
