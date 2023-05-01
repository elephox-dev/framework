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
 * @covers \Elephox\Clock\Duration
 *
 * @internal
 */
final class PeriodIteratorTest extends TestCase
{
	public function testPeriodIterator(): void
	{
		$start = new FrozenClock(new DateTimeImmutable('2018-01-01'));
		$end = new FrozenClock(new DateTimeImmutable('2018-01-02'));
		$iterator = new PeriodIterator($start, new Duration(days: 1), $end);

		self::assertSame($start, $iterator->getStart());
		self::assertSame($end, $iterator->getEnd());
		self::assertSame((new Duration(days: 1))->getTotalMicroseconds(), $iterator->getTotalDuration()->getTotalMicroseconds());

		$iterator->rewind();

		self::assertTrue($iterator->valid());
		self::assertTrue($iterator->current()->equals($start));
		self::assertTrue($iterator->getOffset()->equals(new Duration(days: 0)));
		self::assertSame(0, $iterator->key());
		self::assertTrue($iterator->valid());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertTrue($iterator->current()->equals($end));
		self::assertTrue($iterator->getOffset()->equals(new Duration(days: 1)));
		self::assertSame(1, $iterator->key());

		$iterator->next();

		self::assertFalse($iterator->valid());
	}

	public function testInfiniteIterator(): void
	{
		$start = new FrozenClock(new DateTimeImmutable('2018-01-01'));
		$iterator = new PeriodIterator($start, new Duration(days: 1));

		self::assertSame($start, $iterator->getStart());
		self::assertNull($iterator->getEnd());
		self::assertNull($iterator->getTotalDuration());

		$iterator->rewind();

		self::assertTrue($iterator->valid());
		self::assertTrue($iterator->current()->equals($start));
		self::assertTrue($iterator->getOffset()->equals(new Duration(days: 0)));
		self::assertSame(0, $iterator->key());
		self::assertTrue($iterator->valid());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertTrue($iterator->current()->equals($start->add(new Duration(days: 1))));
		self::assertTrue($iterator->getOffset()->equals(new Duration(days: 1)));
		self::assertSame(1, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
		self::assertTrue($iterator->current()->equals($start->add(new Duration(days: 2))));
		self::assertTrue($iterator->getOffset()->equals(new Duration(days: 2)));
		self::assertSame(2, $iterator->key());

		$iterator->next();

		self::assertTrue($iterator->valid());
	}
}
