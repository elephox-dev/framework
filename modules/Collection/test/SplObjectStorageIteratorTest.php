<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Iterator\SplObjectStorageIterator;
use Iterator;
use PHPUnit\Framework\TestCase;
use SplObjectStorage;
use stdClass;

/**
 * @covers \Elephox\Collection\Iterator\SplObjectStorageIterator
 *
 * @internal
 */
final class SplObjectStorageIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$storage = new SplObjectStorage();
		$storage->attach(new stdClass(), 'foo');
		$storage->attach(new stdClass(), 'bar');

		$iterator = new SplObjectStorageIterator($storage);

		self::assertInstanceOf(Iterator::class, $iterator);

		foreach ($iterator as $obj => $value) {
			self::assertInstanceOf(stdClass::class, $obj);
			self::assertIsString($value);
		}
	}
}
