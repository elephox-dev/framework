<?php
declare(strict_types=1);

namespace Elephox\Promises;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * @covers \Elephox\Promises\Promise
 */
class PromiseTest extends TestCase
{
	/**
	 * @throws \Throwable
	 */
	public function testPromiseResolves(): void
	{
		$promise = new Promise(function () {
			return 'foo';
		});

		$promise = $promise->then(function (string $value): string {
			return strtoupper($value);
		})->then(function (string $value): string {
			return $value . 'bar';
		});

		self::assertEquals('FOObar', $promise->await());
	}

	/**
	 * @throws \Throwable
	 */
	public function testPromiseCatchesThrows(): void
	{
		$caught = false;
		$ex = null;

		$promise = Promise::me(static function () {
			return 'foo';
		})->then(static function (string $value): never {
			throw new RuntimeException($value);
		})->catch(static function (Throwable $e) use (&$caught, &$ex): void {
			$caught = true;
			$ex = $e;
		});

		$promise->await();

		self::assertTrue($caught);
		self::assertInstanceOf(RuntimeException::class, $ex);
		self::assertEquals('foo', $ex->getMessage());
	}

	/**
	 * @throws \Throwable
	 */
	public function testFinallyGetsAlwaysCalled(): void
	{
		$finalized = false;

		Promise::me(static function () {
			return 'foo';
		})->finally(static function () use (&$finalized): void {
			$finalized = true;
		})->await();

		self::assertTrue($finalized);

		$finalized = false;

		Promise::me(static function (): string {
			return 'foo';
		})->then(static function (string $value): never {
			throw new RuntimeException($value);
		})->catch(static function (): void {
			// do nothing
		})->finally(static function () use (&$finalized): void {
			$finalized = true;
		})->await();

		self::assertTrue($finalized);
	}
}
