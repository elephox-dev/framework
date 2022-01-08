<?php
declare(strict_types=1);

namespace Elephox\Support;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

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
		$o1 = new stdClass();
		$o2 = new stdClass();
		Cloneable::$staticProperty = $o1;
		Cloneable::$anotherStaticProperty = $o2;

		$object->deepClone();

		self::assertSame($o1, Cloneable::$staticProperty);
		self::assertSame($o2, Cloneable::$anotherStaticProperty);
	}
}

class Cloneable
{
	use DeepCloneable;

	public ?ThrowOnClone $throwOnClone = null;
	public $resource;

	public static $staticProperty;
	public static $anotherStaticProperty;
}

class ThrowOnClone
{
	public function __clone()
	{
		throw new RuntimeException('Cloning not allowed');
	}
}
