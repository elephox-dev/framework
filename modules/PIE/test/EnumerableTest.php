<?php
declare(strict_types=1);

namespace Elephox\PIE;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\PIE\Enumerable
 * @covers \Elephox\PIE\RangeIterator
 * @covers \Elephox\PIE\SelectIterator
 * @uses \Elephox\PIE\IsEnumerable
 */
class EnumerableTest extends TestCase
{
	public function testSelect(): void
	{
		self::assertEquals(
			[-2, -6, -10],
			Enumerable::range(-1, -5, -2)->select(function (int $x): int {
				return $x * 2;
			})->toArray()
		);
	}
}
