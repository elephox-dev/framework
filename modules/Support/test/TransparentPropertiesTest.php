<?php
declare(strict_types=1);

namespace Elephox\Support;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\GetterSetterPrefixBuilder
 * @covers \Elephox\Support\TransparentProperties
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
final class TransparentPropertiesTest extends TestCase
{
	public function testGetter(): void
	{
		$obj = new ExamplePropertiesClass();
		$obj->setInternalValue(123);
		self::assertSame(123, $obj->getValue());

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('No property for reading could be found using Elephox\Support\ExamplePropertiesClass::getNonExistingProperty. If you intend to set a value, pass at least one argument.');
		$obj->getNonExistingProperty();
	}

	public function testSetter(): void
	{
		$obj = new ExamplePropertiesClass();
		$obj->setValue(123);
		self::assertSame(123, $obj->getInternalValue());

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Unknown method Elephox\Support\ExamplePropertiesClass::setNonExistingProperty()');
		$obj->setNonExistingProperty(456);
	}

	public function testUnitialized(): void
	{
		$obj = new ExamplePropertiesClass();
		$obj->setUninitialized('test');
		self::assertSame('test', $obj->getInternalUninitialized());
	}
}

/**
 * @method int getValue()
 * @method int setValue(int $value)
 * @method string getUninitialized()
 * @method string setUninitialized(string $value)
 */
class ExamplePropertiesClass
{
	use TransparentProperties;

	private int $value = 0;
	private string $uninitialized;

	public function getInternalValue(): int
	{
		return $this->value;
	}

	public function setInternalValue(int $value): int
	{
		return $this->value = $value;
	}

	public function getInternalUninitialized(): string
	{
		return $this->uninitialized;
	}

	public function setInternalUninitialized(string $value): string
	{
		return $this->uninitialized = $value;
	}
}
