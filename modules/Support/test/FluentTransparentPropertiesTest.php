<?php
declare(strict_types=1);

namespace Elephox\Support;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\FluentSetterTransparentGetterProperties
 * @covers \Elephox\Support\GetterSetterPrefixBuilder
 * @covers \Elephox\Support\TransparentProperties
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
final class FluentTransparentPropertiesTest extends TestCase
{
	public function testFluentSetter(): void
	{
		$obj = new ExampleFluentPropertiesTest();

		self::assertSame(0, $obj->getValue());
		$result = $obj->setValue(42);
		self::assertSame($obj, $result);
		self::assertSame(42, $obj->getValue());
	}
}

/**
 * @method self setValue(int $value)
 * @method int getValue()
 */
class ExampleFluentPropertiesTest
{
	use FluentSetterTransparentGetterProperties;

	private int $value = 0;
}
