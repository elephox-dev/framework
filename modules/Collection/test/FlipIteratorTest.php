<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Iterator\FlipIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\FlipIterator
 *
 * @internal
 */
final class FlipIteratorTest extends TestCase
{
	public function testGetInnerIterator(): void
	{
		$it = new ArrayIterator([]);
		$flip = new FlipIterator($it);

		$this->assertSame($it, $flip->getInnerIterator());
	}
}
