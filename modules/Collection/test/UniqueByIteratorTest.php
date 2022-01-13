<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\UniqueByIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\UniqueByIterator
 * @covers \Elephox\Collection\DefaultEqualityComparer
 */
class UniqueByIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$iterator = new UniqueByIterator(new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]), fn($value) => $value % 2, DefaultEqualityComparer::same(...));

		self::assertEquals(['a' => 1, 'b' => 2], iterator_to_array($iterator));
	}
}
