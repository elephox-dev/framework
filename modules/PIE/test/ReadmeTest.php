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
		$array = [1, 2, 3, 4, 5];
		$pie = PIE::from($array);

		$sum = $pie->sum(fn (int $item) => $item);

		self::assertEquals(15, $sum);

		$sum2 = $pie->where(fn(int $item) => $item % 2 === 0)
					->sum(fn (int $item) => $item);

		self::assertEquals(6, $sum2);
	}
}
