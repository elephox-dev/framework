<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\ReverseIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\ReverseIterator
 *
 * @internal
 */
final class ReverseIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$arrayIterator = new ArrayIterator([1, 2, 3]);
		$iterator = new ReverseIterator($arrayIterator, false);

		self::assertSame([3, 2, 1], iterator_to_array($iterator));
		self::assertCount(3, $iterator);
	}
}
