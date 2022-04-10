<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\DI\ServiceDescriptor
 * @covers \Elephox\DI\InvalidServiceDescriptorException
 *
 * @internal
 */
class ServiceDescriptorTest extends TestCase
{
	public function testConstructor(): void
	{
		$sd = new ServiceDescriptor(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, new TestServiceClass());

		static::assertInstanceOf(TestServiceInterface::class, $sd->instance);
	}

	public function testEitherFactoryOrInstanceMustBeSet(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Either implementationFactory or instance must be set.');

		new ServiceDescriptor(
			TestServiceClass::class,
			TestServiceClass::class,
			ServiceLifetime::Singleton,
			null,
			null,
		);
	}

	public function testTransientServiceRequiresFactory(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Transient service must have implementationFactory set.');

		new ServiceDescriptor(
			stdClass::class,
			stdClass::class,
			ServiceLifetime::Transient,
			null,
			new TestServiceClass(),
		);
	}

	public function testInstanceMustBeOfImplementationType(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Instance must be of given implementation type (' . stdClass::class . '). Given instance is of type ' . TestServiceClass::class . '.');

		new ServiceDescriptor(
			stdClass::class,
			stdClass::class,
			ServiceLifetime::Singleton,
			null,
			new TestServiceClass(),
		);
	}

	public function testInstanceMustBeOfServiceType(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Instance must be of given service type (' . stdClass::class . '). Given instance is of type ' . TestServiceClass::class . '.');

		new ServiceDescriptor(
			stdClass::class,
			TestServiceClass::class,
			ServiceLifetime::Singleton,
			null,
			new TestServiceClass(),
		);
	}
}
