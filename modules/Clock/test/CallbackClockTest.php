<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * @covers \Elephox\Clock\CallbackClock
 *
 * @internal
 */
class CallbackClockTest extends TestCase
{
	public function testConstructor(): void
	{
		$clock = new CallbackClock(static fn () => new DateTimeImmutable());

		static::assertInstanceOf(ClockInterface::class, $clock);
	}

	public function testNow(): void
	{
		$clock = new CallbackClock(static fn () => new DateTimeImmutable());

		static::assertInstanceOf(DateTimeInterface::class, $clock->now());
	}

	public function testCallback(): void
	{
		$clock = new CallbackClock(static fn () => new DateTimeImmutable());
		$a = $clock->now();
		$b = $clock->now();

		static::assertInstanceOf(DateTimeInterface::class, $a);
		static::assertInstanceOf(DateTimeInterface::class, $b);
		static::assertNotSame($a, $b);
	}
}
