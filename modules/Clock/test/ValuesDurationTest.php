<?php
declare(strict_types=1);

namespace Elephox\Clock;

use Elephox\Clock\Contract\Duration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Clock\AbstractDuration
 * @covers \Elephox\Clock\ValuesDuration
 *
 * @internal
 */
class ValuesDurationTest extends TestCase
{
	public function testFromValues(): void
	{
		$duration = ValuesDuration::from(false, 1, 2, 3, 4, 5, 6, 7);

		static::assertFalse($duration->isNegative());
		static::assertEquals(7, $duration->getYears());
		static::assertEquals(6, $duration->getMonths());
		static::assertEquals(5, $duration->getDays());
		static::assertEquals(4, $duration->getHours());
		static::assertEquals(3, $duration->getMinutes());
		static::assertEquals(2, $duration->getSeconds());
		static::assertEquals(1, $duration->getMicroseconds());
	}

	public function totalsDataProvider(): iterable
	{
		yield [ValuesDuration::from(microseconds: 1234, seconds: 1), 'microseconds', 1001234];
		yield [ValuesDuration::from(microseconds: 1000, seconds: 12), 'seconds', 12.001];
		yield [ValuesDuration::from(seconds: 10, minutes: 2), 'seconds', 130];
		yield [ValuesDuration::from(seconds: 15, minutes: 2), 'minutes', 2.25];
		yield [ValuesDuration::from(minutes: 20, hours: 2), 'minutes', 140];
		yield [ValuesDuration::from(minutes: 6, hours: 3), 'hours', 3.1];
		yield [ValuesDuration::from(hours: 4, days: 2), 'hours', 52];
		yield [ValuesDuration::from(hours: 6, days: 3), 'days', 3.25];
		yield [ValuesDuration::from(days: 15, months: 2), 'days', 75.87375];
		yield [ValuesDuration::from(days: 6, months: 3), 'months', 3.1971293045032];
		yield [ValuesDuration::from(months: 15, years: 2), 'months', 39];
		yield [ValuesDuration::from(months: 6, years: 3), 'years', 3.5];
	}

	/**
	 * @dataProvider totalsDataProvider
	 *
	 * @param Duration $duration
	 * @param string $unit
	 * @param float $total
	 */
	public function testTotals(Duration $duration, string $unit, float $total): void
	{
		static::assertEquals($total, $duration->{'getTotal' . ucfirst($unit)}());
	}

	public function testAdd(): void
	{
		$duration = ValuesDuration::from(microseconds: 1234, seconds: 1);
		$duration = $duration->add(ValuesDuration::from(microseconds: 1000, seconds: 12));

		static::assertEquals(ValuesDuration::from(microseconds: 2234, seconds: 13), $duration);
	}

	public function testSubtract(): void
	{
		$a = ValuesDuration::from(microseconds: 1234, seconds: 1);
		$b = ValuesDuration::from(microseconds: 1000, seconds: 12);

		static::assertEquals(ValuesDuration::from(negative: true, microseconds: 234, seconds: 11), $a->subtract($b));
	}

	public function testEquals(): void
	{
		$a = ValuesDuration::from(microseconds: 1234, seconds: 1);
		$b = ValuesDuration::from(negative: true, microseconds: 1000, seconds: 12);

		static::assertTrue($a->equals($a));
		static::assertFalse($a->equals($b));
		static::assertTrue($b->equals($b));
	}

	public function testCompare(): void
	{
		$a = ValuesDuration::from(microseconds: 1234, seconds: 1);
		$b = ValuesDuration::from(negative: true, microseconds: 1000, seconds: 12);

		static::assertEquals(0, $a->compare($a));
		static::assertEquals(0, $b->compare($b));
		static::assertEquals(1, $a->compare($b));
		static::assertEquals(-1, $b->compare($a));

		$c = ValuesDuration::from(years: 1);
		$e = ValuesDuration::from(months: 1, years: 1);
		$f = ValuesDuration::from(days: 1, years: 1);
		$g = ValuesDuration::from(hours: 1, years: 1);
		$h = ValuesDuration::from(minutes: 1, years: 1);
		$i = ValuesDuration::from(seconds: 1, years: 1);
		$j = ValuesDuration::from(microseconds: 1, years: 1);

		static::assertEquals(-1, $c->compare($e));
		static::assertEquals(-1, $c->compare($f));
		static::assertEquals(-1, $c->compare($g));
		static::assertEquals(-1, $c->compare($h));
		static::assertEquals(-1, $c->compare($i));
		static::assertEquals(-1, $c->compare($j));
	}

	public function testToInterval(): void
	{
		$a = AbstractDuration::toInterval(ValuesDuration::from(microseconds: 1234, seconds: 1));

		static::assertEquals(0, $a->invert)
		;
		static::assertEquals(0.001234, $a->f);
		static::assertEquals(1, $a->s);

		$b = AbstractDuration::toInterval(ValuesDuration::from(negative: true, microseconds: 1000, seconds: 12));

		static::assertEquals(1, $b->invert);
		static::assertEquals(0.001, $b->f);
		static::assertEquals(12, $b->s);
	}
}
