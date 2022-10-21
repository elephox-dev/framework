<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use StellaMaris\Clock\ClockInterface;

/**
 * @covers \Elephox\Clock\AbstractClock
 * @covers \Elephox\Clock\AbstractDuration
 * @covers \Elephox\Clock\FrozenClock
 * @covers \Elephox\Clock\ValuesDuration
 * @covers \Elephox\Clock\LazyClock
 *
 * @internal
 */
class FrozenClockTest extends TestCase
{
	public function testConstructor(): void
	{
		$clock = new FrozenClock(new DateTimeImmutable('now'));

		static::assertInstanceOf(ClockInterface::class, $clock);
	}

	public function testNow(): void
	{
		$source = new DateTimeImmutable('now');
		$clock = new FrozenClock($source);

		static::assertSame($source, $clock->now());
	}

	public function testDiff(): void
	{
		$source = new FrozenClock(new DateTimeImmutable('now'));
		$target = new FrozenClock(new DateTimeImmutable('+1 day'));
		$diff = $source->diff($target);
		$diffRev = $target->diff($source);

		static::assertEqualsWithDelta(1, $diff->getTotalDays(), 1.0E-15);
		static::assertFalse($diff->isNegative());
		static::assertEqualsWithDelta(1, $diffRev->getTotalDays(), 1.0E-15);
		static::assertTrue($diffRev->isNegative());
	}

	public function testExtremeDiff(): void
	{
		$source = new FrozenClock(new DateTimeImmutable('0000-00-00 00:00:00.000000'));
		$target = new FrozenClock(new DateTimeImmutable('9999-12-31 23:59:59.999999'));
		$diff = $source->diff($target);
		$diffRev = $target->diff($source);

		static::assertEqualsWithDelta(3652457.4368634, $diff->getTotalDays(), 1.0E-7);
		static::assertFalse($diff->isNegative());
		static::assertEqualsWithDelta(3652457.4368634, $diffRev->getTotalDays(), 1.0E-7);
		static::assertTrue($diffRev->isNegative());
	}

	public function testEqualsAndCompare(): void
	{
		$a = new FrozenClock(new DateTimeImmutable('now'));
		$b = new FrozenClock(new DateTimeImmutable('+1 day'));

		static::assertTrue($a->equals($a));
		static::assertFalse($b->equals($a));
		static::assertTrue($b->equals($b));

		static::assertEquals(0, $a->compare($a));
		static::assertEquals(-1, $a->compare($b));
		static::assertEquals(1, $b->compare($a));
	}

	public function testAddAndSub(): void
	{
		$a = new FrozenClock(new DateTimeImmutable('now'));
		$b = new FrozenClock(new DateTimeImmutable('+1 day'));

		static::assertNotEquals($b->now()->format(DateTimeInterface::ATOM), $a->now()->format(DateTimeInterface::ATOM));
		static::assertEquals($b->now()->format(DateTimeInterface::ATOM), $a->add(new ValuesDuration(days: 1))->now()->format(DateTimeInterface::ATOM));
		static::assertEquals($a->now()->format(DateTimeInterface::ATOM), $b->sub(new ValuesDuration(days: 1))->now()->format(DateTimeInterface::ATOM));
	}
}
