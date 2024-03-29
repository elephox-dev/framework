<?php
declare(strict_types=1);

namespace Elephox\Clock;

use AssertionError;
use DateInterval;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Clock\AbstractDuration
 * @covers \Elephox\Clock\Duration
 *
 * @internal
 */
final class DurationTest extends TestCase
{
	public function testFromValues(): void
	{
		$duration = Duration::from(false, 1, 2, 3, 4, 5, 6, 7);

		self::assertFalse($duration->isNegative());
		self::assertSame(7, $duration->getYears());
		self::assertSame(6, $duration->getMonths());
		self::assertSame(5, $duration->getDays());
		self::assertSame(4, $duration->getHours());
		self::assertSame(3, $duration->getMinutes());
		self::assertSame(2, $duration->getSeconds());
		self::assertSame(1.0, $duration->getMicroseconds());
	}

	public function testFromTotalMicroseconds(): void
	{
		$a = Duration::fromTotalMicroseconds(123456789123456.78125);

		self::assertFalse($a->isNegative());
		self::assertSame(123456.78125, $a->getMicroseconds());
		self::assertSame(9, $a->getSeconds());
		self::assertSame(33, $a->getMinutes());
		self::assertSame(21, $a->getHours());
		self::assertSame(15, $a->getDays());
		self::assertSame(3, $a->getMonths());
		self::assertSame(3, $a->getYears());

		$b = Duration::fromTotalMicroseconds(Contract\Duration::MICROSECONDS_PER_SECOND);

		self::assertFalse($b->isNegative());
		self::assertSame(0.0, $b->getMicroseconds());
		self::assertSame(1, $b->getSeconds());
		self::assertSame(0, $b->getMinutes());
		self::assertSame(0, $b->getHours());
		self::assertSame(0, $b->getDays());
		self::assertSame(0, $b->getMonths());
		self::assertSame(0, $b->getYears());

		$max = Duration::fromTotalMicroseconds(PHP_FLOAT_MAX);

		self::assertFalse($max->isNegative());
		self::assertSame(858368.0, $max->getMicroseconds());
		self::assertSame(0, $max->getSeconds());
		self::assertSame(0, $max->getMinutes());
		self::assertSame(0, $max->getHours());
		self::assertSame(0, $max->getDays());
		self::assertSame(0, $max->getMonths());
		self::assertSame(0, $max->getYears());

		$min = Duration::fromTotalMicroseconds(PHP_FLOAT_MIN);

		self::assertFalse($min->isNegative());
		self::assertSame(PHP_FLOAT_MIN, $min->getMicroseconds());
		self::assertSame(0, $min->getSeconds());
		self::assertSame(0, $min->getMinutes());
		self::assertSame(0, $min->getHours());
		self::assertSame(0, $min->getDays());
		self::assertSame(0, $min->getMonths());
		self::assertSame(0, $min->getYears());
	}

	public function totalsDataProvider(): iterable
	{
		yield [Duration::from(microseconds: 1234, seconds: 1), 'microseconds', 1001234];
		yield [Duration::from(microseconds: 1000, seconds: 12), 'seconds', 12.001];
		yield [Duration::from(seconds: 10, minutes: 2), 'seconds', 130];
		yield [Duration::from(seconds: 15, minutes: 2), 'minutes', 2.25];
		yield [Duration::from(minutes: 20, hours: 2), 'minutes', 140];
		yield [Duration::from(minutes: 6, hours: 3), 'hours', 3.1];
		yield [Duration::from(hours: 4, days: 2), 'hours', 52];
		yield [Duration::from(hours: 6, days: 3), 'days', 3.25];
		yield [Duration::from(days: 15, months: 2), 'days', 75.87375];
		yield [Duration::from(days: 6, months: 3), 'months', 3.1971293045032];
		yield [Duration::from(months: 15, years: 2), 'months', 39];
		yield [Duration::from(months: 6, years: 3), 'years', 3.5];
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
		self::assertEqualsWithDelta($total, $duration->{'getTotal' . ucfirst($unit)}(), 1.0E-13);
	}

	public function testAdd(): void
	{
		$duration = Duration::from(microseconds: 1234, seconds: 1);
		$duration = $duration->add(Duration::from(microseconds: 1000, seconds: 12));

		self::assertSame(Duration::from(microseconds: 2234, seconds: 13)->getTotalMicroseconds(), $duration->getTotalMicroseconds());
	}

	public function testSubtract(): void
	{
		$a = Duration::from(microseconds: 1000, seconds: 1);
		$b = Duration::from(microseconds: 1123, seconds: 1);
		$c = $a->subtract($b);

		self::assertSame(123.0, $c->getTotalMicroseconds());
	}

	public function testEquals(): void
	{
		$a = Duration::from(microseconds: 1234, seconds: 1);
		$b = Duration::from(negative: true, microseconds: 1000, seconds: 12);

		self::assertTrue($a->equals($a));
		self::assertFalse($a->equals($b));
		self::assertTrue($b->equals($b));
	}

	public function testCompare(): void
	{
		$a = Duration::from(microseconds: 1234, seconds: 1);
		$b = Duration::from(negative: true, microseconds: 1000, seconds: 12);

		self::assertSame(0, $a->compare($a));
		self::assertSame(0, $b->compare($b));
		self::assertSame(1, $a->compare($b));
		self::assertSame(-1, $b->compare($a));

		$c = Duration::from(years: 1);
		$d = Duration::from(months: 1, years: 1);
		$e = Duration::from(days: 1, years: 1);
		$f = Duration::from(hours: 1, years: 1);
		$g = Duration::from(minutes: 1, years: 1);
		$h = Duration::from(seconds: 1, years: 1);
		$i = Duration::from(microseconds: 1, years: 1);

		self::assertSame(-1, $c->compare($d));
		self::assertSame(-1, $c->compare($e));
		self::assertSame(-1, $c->compare($f));
		self::assertSame(-1, $c->compare($g));
		self::assertSame(-1, $c->compare($h));
		self::assertSame(-1, $c->compare($i));
	}

	public function testToInterval(): void
	{
		$a = Duration::from(microseconds: 1234, seconds: 1)->toDateInterval();

		self::assertSame(0, $a->invert);
		self::assertSame(0.001234, $a->f);
		self::assertSame(1, $a->s);

		$b = Duration::from(negative: true, microseconds: 1000, seconds: 12)->toDateInterval();

		self::assertSame(1, $b->invert);
		self::assertSame(0.001, $b->f);
		self::assertSame(12, $b->s);
	}

	public function testNegatives(): void
	{
		$this->expectException(AssertionError::class);
		$this->expectExceptionMessage('Microseconds must be greater than or equal to 0. To represent negative durations, pass "negative: true"');

		Duration::from(microseconds: -1);
	}

	public function testZero(): void
	{
		$zeroA = Duration::zero();
		$zeroB = Duration::zero();

		self::assertSame($zeroA, $zeroB);
		self::assertSame(0.0, $zeroA->getTotalMicroseconds());
		self::assertFalse($zeroA->isNegative());

		$modified = $zeroA->add(Duration::from(seconds: 1));
		self::assertSame(0.0, $zeroA->getTotalMicroseconds());
		self::assertSame(1.0, $modified->getTotalSeconds());
	}

	public function testToFromDateInterval(): void
	{
		$i = new DateInterval('P1DT1S');
		$a = Duration::fromDateInterval($i);

		self::assertFalse($a->isNegative());
		self::assertSame(0.0, $a->getMicroseconds());
		self::assertSame(1, $a->getSeconds());
		self::assertSame(0, $a->getMinutes());
		self::assertSame(0, $a->getHours());
		self::assertSame(1, $a->getDays());
		self::assertSame(0, $a->getMonths());
		self::assertSame(0, $a->getYears());

		$b = $a->toDateInterval();

		self::assertSame(0, $b->invert);
		self::assertSame(0.0, $b->f);
		self::assertSame(1, $b->s);
		self::assertSame(0, $b->i);
		self::assertSame(0, $b->h);
		self::assertSame(1, $b->d);
		self::assertSame(0, $b->m);
		self::assertSame(0, $b->y);
	}

	public function testAbs(): void
	{
		$d = Duration::from(negative: true, seconds: 30, minutes: 1)->abs();

		self::assertFalse($d->isNegative());
		self::assertSame(0.0, $d->getMicroseconds());
		self::assertSame(30, $d->getSeconds());
		self::assertSame(1, $d->getMinutes());
		self::assertSame(0, $d->getHours());
		self::assertSame(0, $d->getDays());
		self::assertSame(0, $d->getMonths());
		self::assertSame(0, $d->getYears());
	}

	public function testToString(): void
	{
		$d1 = Duration::from(
			microseconds: 1.234,
			seconds: 5,
			minutes: 6,
			hours: 7,
			days: 8,
			months: 9,
			years: 10,
		);

		self::assertSame('Duration(8.9.10 7:6:5:1.234)', (string) $d1);

		$d2 = Duration::from(negative: true, microseconds: 0.001);

		self::assertSame('Duration(neg 0.0.0 0:0:0:0.001)', (string) $d2);
	}
}
