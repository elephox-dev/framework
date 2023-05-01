<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Iterator\EagerCachingIterator;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 *
 * @internal
 */
final class EagerCachingIteratorTest extends TestCase
{
	public function testGeneratorIsRewindable(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertSame(1, $iterator->current());
		self::assertTrue($iterator->hasNext());
		$iterator->next();
		self::assertSame(2, $iterator->current());
		self::assertTrue($iterator->hasNext());
		$iterator->next();
		self::assertSame(3, $iterator->current());
		self::assertFalse($iterator->hasNext());
		$iterator->rewind();
		self::assertSame(1, $iterator->current());
		self::assertTrue($iterator->hasNext());
		$iterator->next();
		self::assertSame(2, $iterator->current());
		self::assertTrue($iterator->hasNext());
	}

	public function testArrayAccess(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertTrue(isset($iterator[0]));
		self::assertSame(1, $iterator[0]);
		self::assertTrue(isset($iterator[1]));
		self::assertSame(2, $iterator[1]);
		self::assertTrue(isset($iterator[2]));
		self::assertSame(3, $iterator[2]);
	}

	public function testCurrentIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertSame(1, $iterator->current());
	}

	public function testKeyIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertSame(0, $iterator->key());
	}

	public function testNextIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		$iterator->next();
		self::assertSame(2, $iterator->current());
	}

	public function testHasNextIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertTrue($iterator->hasNext());
	}

	public function testValidIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertTrue($iterator->valid());
	}

	public function testOffsetExistsIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertTrue(isset($iterator[0]));
	}

	public function testOffsetGetIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertSame(1, $iterator[0]);
	}

	public function testCountIsEager(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		self::assertCount(3, $iterator);
	}

	public function testOffsetSetThrows(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Cannot set values in EagerCachingIterator');

		$iterator->offsetSet(0, 1);
	}

	public function testOffsetUnsetThrows(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Cannot unset values in EagerCachingIterator');

		$iterator->offsetUnset(0);
	}

	public function testGetCacheValues(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		$iterator->rewind();
		self::assertSame([1, 2, 3], $iterator->getCacheValues());
	}

	public function testGetCacheKeys(): void
	{
		$iterator = new EagerCachingIterator((static function () {
			yield 1;
			yield 2;
			yield 3;
		})());

		$iterator->rewind();
		self::assertSame([0, 1, 2], $iterator->getCacheKeys());
	}
}
