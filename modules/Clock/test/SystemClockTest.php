<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * @covers \Elephox\Clock\AbstractClock
 * @covers \Elephox\Clock\SystemClock
 * @covers \Elephox\Clock\LazyClock
 *
 * @internal
 */
final class SystemClockTest extends TestCase
{
	public function testConstructor(): void
	{
		$clock = new SystemClock();

		self::assertInstanceOf(ClockInterface::class, $clock);
	}

	public function testNow(): void
	{
		$clock = new SystemClock();

		self::assertInstanceOf(DateTimeInterface::class, $clock->now());
	}
}
