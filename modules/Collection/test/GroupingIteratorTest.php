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
 *
 * @internal
 */
class GroupingIteratorTest extends TestCase
{
	public function testCurrentThrowsIfEmpty(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('No current group key');

		$iterator = new GroupingIterator(new ArrayIterator([]), static fn ($v) => $v);
		$iterator->rewind();
		$iterator->current();
	}

	public function testKey(): void
	{
		$iterator = new GroupingIterator(new ArrayIterator([1, 2, 3]), static fn ($v) => $v % 2);
		$iterator->rewind();
		static::assertEquals(1, $iterator->key());
		$iterator->next();
		static::assertEquals(0, $iterator->key());
		$group = $iterator->current();
		static::assertEquals($iterator->key(), $group->groupKey());

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('No current group key');

		$emptyIterator = new GroupingIterator(new ArrayIterator([]), static fn ($v) => $v);
		$emptyIterator->rewind();
		$emptyIterator->key();
	}
}
