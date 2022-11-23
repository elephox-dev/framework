<?php
declare(strict_types=1);

namespace Elephox\Support;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\FluidSetterTransparentGetterProperties
 * @covers \Elephox\Support\GetterSetterPrefixBuilder
 * @covers \Elephox\Support\TransparentProperties
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
class FluidTransparentPropertiesTest extends TestCase
{
	public function testIsFluid(): void
	{
		$obj = new ExampleFluidPropertiesTest();

		static::assertSame(0, $obj->getValue());
		$result = $obj->setValue(42);
		static::assertSame($obj, $result);
		static::assertSame(42, $obj->getValue());
	}
}

/**
 * @method self setValue(int $value)
 * @method int getValue()
 */
class ExampleFluidPropertiesTest {
	use FluidSetterTransparentGetterProperties;

	private int $value = 0;
}
