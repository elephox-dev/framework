<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Clock\PeriodIterator
 * @covers \Elephox\Clock\AbstractClock
 * @covers \Elephox\Clock\AbstractDuration
 * @covers \Elephox\Clock\FrozenClock
 * @covers \Elephox\Clock\LazyClock
 * @covers \Elephox\Clock\ValuesDuration
 *
 * @internal
 */
class PeriodIteratorTest extends TestCase
{
	public function testPeriodIterator(): void
	{
		$start = new FrozenClock(new DateTimeImmutable('2018-01-01'));
		$end = new FrozenClock(new DateTimeImmutable('2018-01-02'));
		$iterator = new PeriodIterator($start, new ValuesDuration(days: 1), $end);

		static::assertSame($start, $iterator->getStart());
		static::assertSame($end, $iterator->getEnd());
		static::assertSame((new ValuesDuration(days: 1))->getTotalMicroseconds(), $iterator->getTotalDuration()->getTotalMicroseconds());

		$iterator->rewind();

		static::assertTrue($iterator->valid());
		static::assertTrue($iterator->current()->equals($start));
		static::assertTrue($iterator->currentOffset()->equals(new ValuesDuration(days: 0)));
		static::assertSame(0, $iterator->key());
		static::assertTrue($iterator->valid());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertTrue($iterator->current()->equals($end));
		static::assertTrue($iterator->currentOffset()->equals(new ValuesDuration(days: 1)));
		static::assertSame(1, $iterator->key());

		$iterator->next();

		static::assertFalse($iterator->valid());
	}

	public function testInfiniteIterator(): void
	{
		$start = new FrozenClock(new DateTimeImmutable('2018-01-01'));
		$iterator = new PeriodIterator($start, new ValuesDuration(days: 1));

		static::assertSame($start, $iterator->getStart());
		static::assertNull($iterator->getEnd());
		static::assertNull($iterator->getTotalDuration());

		$iterator->rewind();

		static::assertTrue($iterator->valid());
		static::assertTrue($iterator->current()->equals($start));
		static::assertTrue($iterator->currentOffset()->equals(new ValuesDuration(days: 0)));
		static::assertSame(0, $iterator->key());
		static::assertTrue($iterator->valid());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertTrue($iterator->current()->equals($start->add(new ValuesDuration(days: 1))));
		static::assertTrue($iterator->currentOffset()->equals(new ValuesDuration(days: 1)));
		static::assertSame(1, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
		static::assertTrue($iterator->current()->equals($start->add(new ValuesDuration(days: 2))));
		static::assertTrue($iterator->currentOffset()->equals(new ValuesDuration(days: 2)));
		static::assertSame(2, $iterator->key());

		$iterator->next();

		static::assertTrue($iterator->valid());
	}
}
