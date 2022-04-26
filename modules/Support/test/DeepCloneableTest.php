<?php
declare(strict_types=1);

namespace Elephox\Support;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplObjectStorage;
use stdClass;
use WeakMap;

/**
 * @covers \Elephox\Support\DeepCloneable
 * @covers \Elephox\Support\CloneBehaviour
 *
 * @internal
 */
class DeepCloneableTest extends TestCase
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

		static::assertSame($resource, $object->resource);

		fclose($object->resource);
	}

	public function testEnumMembersStaySame(): void
	{
		$object = new Cloneable();
		$object->enumValue = TestEnum::A;

		$clone = $object->deepClone();

		static::assertSame(TestEnum::A, $clone->enumValue);
	}

	public function testStaticPropertyDoesntChange(): void
	{
		$object = new Cloneable();
		$o1 = new stdClass();
		$o2 = new stdClass();
		Cloneable::$staticProperty = $o1;
		Cloneable::$anotherStaticProperty = $o2;

		$object->deepClone();

		static::assertSame($o1, Cloneable::$staticProperty);
		static::assertSame($o2, Cloneable::$anotherStaticProperty);
	}

	public function testWeakMapKeysAreKept(): void
	{
		$object = new HasWeakMap();
		$o1 = new stdClass();
		$o2 = new stdClass();
		$object->weakMap = new WeakMap();
		$object->weakMap->offsetSet($o1, $o2);

		$clone = $object->deepClone();

		static::assertTrue($clone->weakMap->offsetExists($o1));
		static::assertNotSame($o2, $clone->weakMap->offsetGet($o1));
	}

	public function testObjectStorageIsDeepCloned(): void
	{
		$object = new HasObjectStorage();
		$o1 = new stdClass();
		$o1->test = true;
		$o2 = new stdClass();
		$o2->prop = ['test' => true];
		$object->objectStorage = new SplObjectStorage();
		$object->objectStorage->attach($o1);
		$object->objectStorage->attach($o2);

		$clone = $object->deepClone();

		static::assertFalse($clone->objectStorage->contains($o1));
		static::assertFalse($clone->objectStorage->contains($o2));

		$clone->objectStorage->rewind();
		$o3 = $clone->objectStorage->current();
		$clone->objectStorage->next();
		$o4 = $clone->objectStorage->current();

		static::assertNotSame($o1, $o3);
		static::assertNotSame($o2, $o4);
		static::assertTrue($o3->test);
		static::assertTrue($o4->prop['test']);
	}

	public function testArraysAreDeepCloned(): void
	{
		$object = new Cloneable();
		$object->testArray[] = new stdClass();

		$clone = $object->deepClone();

		static::assertNotSame($object->testArray[0], $clone->testArray[0]);
	}

	public function testReferencesDontGetClonedAgain(): void
	{
		$object = new Cloneable();
		$object->testArray[] = &$object;

		$clone = $object->deepClone();

		static::assertNotSame($object, $clone->testArray[0]);
		static::assertSame($clone, $clone->testArray[0]);
	}

	public function testCloneBehaviour(): void
	{
		$object = new Cloneable();
		$object->sameOnClone = new stdClass();
		$object->sameOnClone->a = 'a';
		$object->leaveDefaultValue = new stdClass();
		$object->leaveDefaultValue->b = 'b';
		$object->normalDeepClone = new stdClass();
		$object->normalDeepClone->c = 'c';
		$object->defaultValue = 456;
		$object->skippedDefaultValue = 456;

		$clone = $object->deepClone();

		static::assertSame($object->sameOnClone, $clone->sameOnClone);
		static::assertNull($clone->leaveDefaultValue);
		static::assertNotSame($object->normalDeepClone, $clone->normalDeepClone);
		static::assertSame($object->normalDeepClone->c, $clone->normalDeepClone->c);
		static::assertSame($object->defaultValue, $clone->defaultValue);
		static::assertNotSame($object->skippedDefaultValue, $clone->skippedDefaultValue);
	}
}

class Cloneable
{
	use DeepCloneable;

	public ?ThrowOnClone $throwOnClone = null;
	public static $staticProperty;

	public $resource;

	public static $anotherStaticProperty;

	public ?TestEnum $enumValue = null;

	public array $testArray = ['a' => true, 'b' => [1, 2, 3]];

	#[CloneBehaviour(CloneAction::Skip)]
	public ?stdClass $leaveDefaultValue = null;

	public int $defaultValue = 123;

	#[CloneBehaviour(CloneAction::Skip)]
	public int $skippedDefaultValue = 123;

	#[CloneBehaviour(CloneAction::Keep)]
	public ?stdClass $sameOnClone = null;

	#[CloneBehaviour(CloneAction::Clone)]
	public ?stdClass $normalDeepClone = null;
}

class ThrowOnClone
{
	public function __clone()
	{
		throw new RuntimeException('Cloning not allowed');
	}
}

class HasWeakMap
{
	use DeepCloneable;

	public WeakMap $weakMap;
}

class HasObjectStorage
{
	use DeepCloneable;

	public SplObjectStorage $objectStorage;
}

enum TestEnum
{
	case A;
	case B;
}
