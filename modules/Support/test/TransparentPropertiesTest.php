<?php
declare(strict_types=1);

namespace Elephox\Support;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\TransparentProperties
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
class TransparentPropertiesTest extends TestCase
{
	public function testGetter(): void
	{
		$obj = new ExamplePropertiesClass();
		$obj->setInternalValue(123);
		static::assertSame(123, $obj->getValue());

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('No property for reading could be found using Elephox\Support\ExamplePropertiesClass::getNonExistingProperty. If you intend to set a value, pass at least one argument.');
		$obj->getNonExistingProperty();
	}

	public function testSetter(): void
	{
		$obj = new ExamplePropertiesClass();
		$obj->setValue(123);
		static::assertSame(123, $obj->getInternalValue());

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Unknown method Elephox\Support\ExamplePropertiesClass::setNonExistingProperty()');
		$obj->setNonExistingProperty(456);
	}
}

/**
 * @method int getValue()
 * @method void setValue(int $value)
 */
class ExamplePropertiesClass {
	use TransparentProperties;

	private int $value = 0;

	public function getInternalValue(): int {
		return $this->value;
	}

	public function setInternalValue(int $value): void {
		$this->value = $value;
	}
}
