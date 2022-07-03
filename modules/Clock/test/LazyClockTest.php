<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use StellaMaris\Clock\ClockInterface;

/**
 * @covers \Elephox\Clock\LazyClock
 *
 * @internal
 */
class LazyClockTest extends TestCase
{
	public function testConstructor(): void
	{
		$clock = new LazyClock(static fn () => new DateTimeImmutable());

		static::assertInstanceOf(ClockInterface::class, $clock);
	}

	public function testNow(): void
	{
		$clock = new LazyClock(static fn () => new DateTimeImmutable());

		static::assertInstanceOf(DateTimeInterface::class, $clock->now());
	}

	public function testCallback(): void
	{
		$clock = new LazyClock(static fn () => new DateTimeImmutable());
		$a = $clock->now();
		$b = $clock->now();

		static::assertInstanceOf(DateTimeInterface::class, $a);
		static::assertInstanceOf(DateTimeInterface::class, $b);
		static::assertSame($a, $b);
	}
}
