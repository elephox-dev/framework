<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\KeySelectIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 *
 * @internal
 */
final class KeySelectIteratorTest extends TestCase
{
	public function testGetInnerIterator(): void
	{
		$it = new ArrayIterator([]);
		$keySelect = new KeySelectIterator($it, fn ($v) => $v);

		$this->assertSame($it, $keySelect->getInnerIterator());
	}
}
