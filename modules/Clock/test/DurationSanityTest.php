<?php
declare(strict_types=1);

namespace Elephox\Clock;

use Elephox\Clock\Contract\Duration;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @uses \Elephox\Clock\Contract\Duration
 *
 * @internal
 */
class DurationSanityTest extends TestCase
{
	public function testDurationConversion(): void
	{
		static::assertSame(1000000, Duration::MICROSECONDS_PER_SECOND);
		static::assertSame(60000000, Duration::MICROSECONDS_PER_MINUTE);
		static::assertSame(3600000000, Duration::MICROSECONDS_PER_HOUR);
		static::assertSame(86400000000, Duration::MICROSECONDS_PER_DAY);
		static::assertSame(2629746000000, Duration::MICROSECONDS_PER_MONTH);
		static::assertSame(31556952000000, Duration::MICROSECONDS_PER_YEAR);

		static::assertSame(1.0E-6, Duration::SECONDS_PER_MICROSECOND);
		static::assertSame(60, Duration::SECONDS_PER_MINUTE);
		static::assertSame(3600, Duration::SECONDS_PER_HOUR);
		static::assertSame(86400, Duration::SECONDS_PER_DAY);
		static::assertSame(2629746, Duration::SECONDS_PER_MONTH);
		static::assertSame(31556952, Duration::SECONDS_PER_YEAR);

		static::assertEqualsWithDelta(1.6666666666667E-8, Duration::MINUTES_PER_MICROSECOND, 1.0E-21);
		static::assertEqualsWithDelta(0.016666666666666, Duration::MINUTES_PER_SECOND, 1.0E-15);
		static::assertSame(60, Duration::MINUTES_PER_HOUR);
		static::assertSame(1440, Duration::MINUTES_PER_DAY);
		static::assertSame(43829.1, Duration::MINUTES_PER_MONTH);
		static::assertSame(525949.2, Duration::MINUTES_PER_YEAR);

		static::assertEqualsWithDelta(2.7777777777778E-10, Duration::HOURS_PER_MICROSECOND, 1.0E-21);
		static::assertEqualsWithDelta(0.0002777777777777, Duration::HOURS_PER_SECOND, 1.0E-15);
		static::assertEqualsWithDelta(0.016666666666667, Duration::HOURS_PER_MINUTE, 1.0E-15);
		static::assertSame(24, Duration::HOURS_PER_DAY);
		static::assertSame(730.485, Duration::HOURS_PER_MONTH);
		static::assertSame(8765.82, Duration::HOURS_PER_YEAR);

		static::assertEqualsWithDelta(1.1574074074074E-11, Duration::DAYS_PER_MICROSECOND, 1.0E-22);
		static::assertEqualsWithDelta(0.0000115740740741, Duration::DAYS_PER_SECOND, 1.0E-16);
		static::assertEqualsWithDelta(0.00069444444444444, Duration::DAYS_PER_MINUTE, 1.0E-15);
		static::assertEqualsWithDelta(0.041666666666667, Duration::DAYS_PER_HOUR, 1.0E-15);
		static::assertSame(30.436875, Duration::DAYS_PER_MONTH);
		static::assertSame(365.2425, Duration::DAYS_PER_YEAR);

		static::assertEqualsWithDelta(3.8026486208174E-13, Duration::MONTHS_PER_MICROSECOND, 1.0E-26);
		static::assertEqualsWithDelta(3.8026486208174E-7, Duration::MONTHS_PER_SECOND, 1.0E-20);
		static::assertEqualsWithDelta(2.2815891724904E-5, Duration::MONTHS_PER_MINUTE, 1.0E-18);
		static::assertEqualsWithDelta(0.0013689535034943, Duration::MONTHS_PER_HOUR, 1.0E-15);
		static::assertEqualsWithDelta(0.032854884083862, Duration::MONTHS_PER_DAY, 1.0E-15);
		static::assertSame(12, Duration::MONTHS_PER_YEAR);

		static::assertEqualsWithDelta(3.1688738506811E-14, Duration::YEARS_PER_MICROSECOND, 1.0E-27);
		static::assertEqualsWithDelta(3.1688738506811E-8, Duration::YEARS_PER_SECOND, 1.0E-21);
		static::assertEqualsWithDelta(1.9013243104087E-6, Duration::YEARS_PER_MINUTE, 1.0E-19);
		static::assertEqualsWithDelta(0.00011407945862452, Duration::YEARS_PER_HOUR, 1.0E-15);
		static::assertEqualsWithDelta(0.0027379070069885, Duration::YEARS_PER_DAY, 1.0E-15);
		static::assertEqualsWithDelta(0.083333333333333, Duration::YEARS_PER_MONTH, 1.0E-15);
	}
}
