<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\SelectIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\SelectIterator
 *
 * @internal
 */
final class SelectIteratorTest extends TestCase
{
	public function testGetInnerIterator(): void
	{
		$it = new ArrayIterator([]);
		$select = new SelectIterator($it, static fn ($v) => $v);

		self::assertSame($it, $select->getInnerIterator());
	}
}
