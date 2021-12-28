<?php
declare(strict_types=1);

namespace Elephox\PIE;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\PIE\PIE
 */
class ReadmeTest extends TestCase
{
	public function testReadme(): void
	{
		$array = [5, 2, 1, 4, 3];
		$pie = PIE::from($array);

		$sum = $pie->sum(fn (int $item) => $item);

		self::assertEquals(15, $sum);

		$sum2 = $pie->where(fn (int $item) => $item % 2 === 0)
					->sum(fn (int $item) => $item);

		self::assertEquals(6, $sum2);

		$ordered = $pie->orderBy(fn($a, $b) => $a <=> $b);

		self::assertEquals([1, 2, 3, 4, 5], $ordered->toArray());
	}
}
