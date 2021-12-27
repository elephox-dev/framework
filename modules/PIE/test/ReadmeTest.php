<?php
declare(strict_types=1);

namespace Elephox\PIE;

use PHPUnit\Framework\TestCase;

class ReadmeTest extends TestCase
{
	public function testReadme(): void
	{
		$array = [1, 2, 3, 4, 5];
		$pie = PIE::from($array);

		$output1 = "";
		$pie->select(function (int $item) use (&$output1) {
			$output1 .= $item;
		});

		self::assertEquals("12345", $output1);

		$output2 = "";
		$pie->where(fn(int $item) => $item % 2 === 0)
			->select(function (int $item) use (&$output2) {
				$output2 .= $item;
			});

		self::assertEquals("24", $output2);
	}
}
