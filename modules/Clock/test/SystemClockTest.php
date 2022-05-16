<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use StellaMaris\Clock\ClockInterface;

/**
 * @covers \Elephox\Clock\AbstractClock
 * @covers \Elephox\Clock\SystemClock
 * @covers \Elephox\Clock\LazyClock
 *
 * @internal
 */
class SystemClockTest extends TestCase
{
	public function testConstructor(): void
	{
		$clock = new SystemClock();

		static::assertInstanceOf(ClockInterface::class, $clock);
	}

	public function testNow(): void
	{
		$clock = new SystemClock();

		static::assertInstanceOf(DateTimeInterface::class, $clock->now());
	}
}
