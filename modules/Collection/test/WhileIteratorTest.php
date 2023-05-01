<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\WhileIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\WhileIterator
 *
 * @internal
 */
final class WhileIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$arrayIterator = new ArrayIterator([1, 2, 3]);
		$iterator = new WhileIterator($arrayIterator, static fn ($value) => $value < 3);

		self::assertSame([1, 2], iterator_to_array($iterator));
		self::assertSame($arrayIterator, $iterator->getInnerIterator());
	}
}
