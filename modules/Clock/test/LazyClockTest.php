<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * @covers \Elephox\Clock\LazyClock
 *
 * @internal
 */
final class LazyClockTest extends TestCase
{
	public function testConstructor(): void
	{
		$clock = new LazyClock(static fn () => new DateTimeImmutable());

		self::assertInstanceOf(ClockInterface::class, $clock);
	}

	public function testNow(): void
	{
		$clock = new LazyClock(static fn () => new DateTimeImmutable());

		self::assertInstanceOf(DateTimeInterface::class, $clock->now());
	}

	public function testCallback(): void
	{
		$clock = new LazyClock(static fn () => new DateTimeImmutable());
		$a = $clock->now();
		$b = $clock->now();

		self::assertInstanceOf(DateTimeInterface::class, $a);
		self::assertInstanceOf(DateTimeInterface::class, $b);
		self::assertSame($a, $b);
	}
}
