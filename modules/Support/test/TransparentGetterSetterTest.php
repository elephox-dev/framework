<?php
declare(strict_types=1);

namespace Elephox\Support;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\GetterSetterPrefixBuilder
 * @covers \Elephox\Support\TransparentGetterSetter
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
class TransparentGetterSetterTest extends TestCase
{
	public function testGetter(): void
	{
		$obj = new ExampleGetterSetterTestClass();
		$obj->internalValue = 123;
		static::assertSame(123, $obj->value);

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('None of the tried getter methods exists: getNotExisting, isNotExisting, hasNotExisting');
		$obj->notExisting;
	}

	public function testSetter(): void
	{
		$obj = new ExampleGetterSetterTestClass();
		$obj->value = 123;
		static::assertSame(123, $obj->internalValue);

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('None of the tried setter methods exists: setNotExisting, putNotExisting');
		$obj->notExisting = 456;
	}

	public function testIsset(): void
	{
		$obj = new ExampleGetterSetterTestClass();
		$obj->internalValue = 123;
		static::assertTrue(isset($obj->value));
		static::assertFalse(isset($obj->notExisting));
	}
}

/**
 * @property int $value
 */
class ExampleGetterSetterTestClass
{
	use TransparentGetterSetter;

	public int $internalValue = 1;

	public function setValue(int $value): void
	{
		$this->internalValue = $value;
	}

	public function getValue(): int
	{
		return $this->internalValue;
	}
}
