<?php
declare(strict_types=1);

namespace Elephox\Support;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Support\DeepCloneable
 */
class DeepCloneTest extends TestCase
{
	public function testThrowsRuntimeException(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Cloning of ' . Cloneable::class . ' failed.');

		$object = new Cloneable();
		$object->throwOnClone = new ThrowOnClone();
		$object->deepClone();
	}

	public function testCloneResourceStaysSame(): void
	{
		$resource = fopen('php://memory', 'rb');

		$object = new Cloneable();
		$object->resource = $resource;
		$object->deepClone();

		self::assertSame($resource, $object->resource);

		fclose($object->resource);
	}

	public function testStaticPropertyDoesntChange(): void
	{
		$object = new Cloneable();
		Cloneable::$staticProperty = 'static';

		$object->deepClone();

		self::assertSame('static', Cloneable::$staticProperty);
	}
}

class Cloneable
{
	use DeepCloneable;

	public ?ThrowOnClone $throwOnClone = null;
	public $resource;

	public static $staticProperty;
}

class ThrowOnClone
{
	public function __clone()
	{
		throw new RuntimeException('Cloning not allowed');
	}
}
