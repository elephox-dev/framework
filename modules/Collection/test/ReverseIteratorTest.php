<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\ReverseIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\ReverseIterator
 */
class ReverseIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$arrayIterator = new ArrayIterator([1, 2, 3]);
		$iterator = new ReverseIterator($arrayIterator);

		self::assertEquals([3, 2, 1], iterator_to_array($iterator));
		self::assertSame($arrayIterator, $iterator->getInnerIterator());
		self::assertCount(3, $iterator);
	}
}
