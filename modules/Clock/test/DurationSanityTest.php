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
		static::assertEquals(1000000, Duration::MICROSECONDS_PER_SECOND);
		static::assertEquals(60000000, Duration::MICROSECONDS_PER_MINUTE);
		static::assertEquals(3600000000, Duration::MICROSECONDS_PER_HOUR);
		static::assertEquals(86400000000, Duration::MICROSECONDS_PER_DAY);
		static::assertEquals(2629746000000, Duration::MICROSECONDS_PER_MONTH);
		static::assertEquals(31556952000000, Duration::MICROSECONDS_PER_YEAR);

		static::assertEquals(1.0E-6, Duration::SECONDS_PER_MICROSECOND);
		static::assertEquals(60, Duration::SECONDS_PER_MINUTE);
		static::assertEquals(3600, Duration::SECONDS_PER_HOUR);
		static::assertEquals(86400, Duration::SECONDS_PER_DAY);
		static::assertEquals(2629746, Duration::SECONDS_PER_MONTH);
		static::assertEquals(31556952, Duration::SECONDS_PER_YEAR);

		static::assertEquals(1.6666666666667E-8, Duration::MINUTES_PER_MICROSECOND);
		static::assertEquals(0.016666666666667, Duration::MINUTES_PER_SECOND);
		static::assertEquals(60, Duration::MINUTES_PER_HOUR);
		static::assertEquals(1440, Duration::MINUTES_PER_DAY);
		static::assertEquals(43829.1, Duration::MINUTES_PER_MONTH);
		static::assertEquals(525949.2, Duration::MINUTES_PER_YEAR);

		static::assertEquals(2.7777777777778E-10, Duration::HOURS_PER_MICROSECOND);
		static::assertEquals(0.0002777777777778, Duration::HOURS_PER_SECOND);
		static::assertEquals(0.016666666666667, Duration::HOURS_PER_MINUTE);
		static::assertEquals(24, Duration::HOURS_PER_DAY);
		static::assertEquals(730.485, Duration::HOURS_PER_MONTH);
		static::assertEquals(8765.82, Duration::HOURS_PER_YEAR);

		static::assertEquals(3.6E-12, Duration::DAYS_PER_MICROSECOND);
		static::assertEquals(0.0000115740740741, Duration::DAYS_PER_SECOND);
		static::assertEquals(0.00069444444444444, Duration::DAYS_PER_MINUTE);
		static::assertEquals(0.041666666666667, Duration::DAYS_PER_HOUR);
		static::assertEquals(30.436875, Duration::DAYS_PER_MONTH);
		static::assertEquals(365.2425, Duration::DAYS_PER_YEAR);

		static::assertEquals(3.1556952E-13, Duration::MONTHS_PER_MICROSECOND);
		static::assertEquals(3.8026486208174E-7, Duration::MONTHS_PER_SECOND);
		static::assertEquals(2.2815891724904E-5, Duration::MONTHS_PER_MINUTE);
		static::assertEquals(0.0013689535034943, Duration::MONTHS_PER_HOUR);
		static::assertEquals(0.032854884083862, Duration::MONTHS_PER_DAY);
		static::assertEquals(12, Duration::MONTHS_PER_YEAR);

		static::assertEquals(3.1556952E-16, Duration::YEARS_PER_MICROSECOND);
		static::assertEquals(3.1688087814596E-8, Duration::YEARS_PER_SECOND);
		static::assertEquals(1.9013243104087E-6, Duration::YEARS_PER_MINUTE);
		static::assertEquals(0.00011407945862452, Duration::YEARS_PER_HOUR);
		static::assertEquals(0.0027379070069885, Duration::YEARS_PER_DAY);
		static::assertEquals(0.083333333333333, Duration::YEARS_PER_MONTH);
	}
}
