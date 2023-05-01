<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * @covers \Elephox\Clock\AbstractClock
 * @covers \Elephox\Clock\AbstractDuration
 * @covers \Elephox\Clock\FrozenClock
 * @covers \Elephox\Clock\Duration
 * @covers \Elephox\Clock\LazyClock
 *
 * @internal
 */
final class FrozenClockTest extends TestCase
{
	public function testConstructor(): void
	{
		$clock = new FrozenClock(new DateTimeImmutable('now'));

		self::assertInstanceOf(ClockInterface::class, $clock);
	}

	public function testNow(): void
	{
		$source = new DateTimeImmutable('now');
		$clock = new FrozenClock($source);

		self::assertSame($source, $clock->now());
	}

	public function testDiff(): void
	{
		$source = new FrozenClock(new DateTimeImmutable('now'));
		$target = new FrozenClock(new DateTimeImmutable('+1 day'));
		$diff = $source->diff($target);
		$diffRev = $target->diff($source);

		self::assertEqualsWithDelta(1, $diff->getTotalDays(), 1.0E-15);
		self::assertFalse($diff->isNegative());
		self::assertEqualsWithDelta(1, $diffRev->getTotalDays(), 1.0E-15);
		self::assertTrue($diffRev->isNegative());
	}

	public function testExtremeDiff(): void
	{
		$source = new FrozenClock(new DateTimeImmutable('0000-00-00 00:00:00.000000'));
		$target = new FrozenClock(new DateTimeImmutable('9999-12-31 23:59:59.999999'));
		$diff = $source->diff($target);
		$diffRev = $target->diff($source);

		self::assertEqualsWithDelta(3652457.4368634, $diff->getTotalDays(), 1.0E-7);
		self::assertFalse($diff->isNegative());
		self::assertEqualsWithDelta(3652457.4368634, $diffRev->getTotalDays(), 1.0E-7);
		self::assertTrue($diffRev->isNegative());
	}

	public function testEqualsAndCompare(): void
	{
		$a = new FrozenClock(new DateTimeImmutable('now'));
		$b = new FrozenClock(new DateTimeImmutable('+1 day'));

		self::assertTrue($a->equals($a));
		self::assertFalse($b->equals($a));
		self::assertTrue($b->equals($b));

		self::assertSame(0, $a->compare($a));
		self::assertSame(-1, $a->compare($b));
		self::assertSame(1, $b->compare($a));
	}

	public function testAddAndSub(): void
	{
		$a = new FrozenClock(new DateTimeImmutable('now'));
		$b = new FrozenClock(new DateTimeImmutable('+1 day'));

		self::assertNotSame($b->now()->format(DateTimeInterface::ATOM), $a->now()->format(DateTimeInterface::ATOM));
		self::assertSame($b->now()->format(DateTimeInterface::ATOM), $a->add(new Duration(days: 1))->now()->format(DateTimeInterface::ATOM));
		self::assertSame($a->now()->format(DateTimeInterface::ATOM), $b->sub(new Duration(days: 1))->now()->format(DateTimeInterface::ATOM));
	}
}
