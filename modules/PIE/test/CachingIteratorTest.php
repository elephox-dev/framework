<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\PIE\CachingIterator
 */
class CachingIteratorTest extends TestCase
{
	/**
	 * @throws \Exception
	 */
	public function testIteration(): void
	{
		$iterations = 0;
		$identityGenerator = static function (int $i) use (&$iterations): Generator {
			for (; $iterations < $i; $iterations++) {
				yield $iterations;
			}

			return $i;
		};

		$iterator = new CachingIterator($identityGenerator(5));

		$iterator->rewind();
		self::assertEquals(0, $iterations);

		self::assertTrue($iterator->valid());
		self::assertEquals(0, $iterator->key());
		self::assertEquals(0, $iterator->current());
		self::assertEquals(1, $iterations);

		$iterator->next();
		self::assertTrue($iterator->valid());
		self::assertEquals(1, $iterator->key());
		self::assertEquals(1, $iterator->current());
		self::assertEquals(2, $iterations);

		$iterator->next();
		self::assertTrue($iterator->valid());
		self::assertEquals(2, $iterator->key());
		self::assertEquals(2, $iterator->current());
		self::assertEquals(3, $iterations);

		$iterator->seek(1);
		self::assertTrue($iterator->valid());
		self::assertEquals(1, $iterator->key());
		self::assertEquals(1, $iterator->current());
		self::assertEquals(3, $iterations);

		$iterator->next();
		self::assertTrue($iterator->valid());
		self::assertEquals(2, $iterator->key());
		self::assertEquals(2, $iterator->current());
		self::assertEquals(3, $iterations);

		$iterator->next();
		self::assertTrue($iterator->valid());
		self::assertEquals(3, $iterator->key());
		self::assertEquals(3, $iterator->current());
		self::assertEquals(4, $iterations);

		$iterator->next();
		self::assertTrue($iterator->valid());
		self::assertEquals(4, $iterator->key());
		self::assertEquals(4, $iterator->current());
		self::assertEquals(5, $iterations);

		$iterator->next();
		self::assertFalse($iterator->valid());
		self::assertNull($iterator->key());
		self::assertNull($iterator->current());

		$iterator->rewind();
		self::assertTrue($iterator->valid());
		self::assertEquals(0, $iterator->key());
		self::assertEquals(0, $iterator->current());
		self::assertEquals(5, $iterations);

	}
}
