<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceClass2;
use Elephox\DI\Data\TestServiceInterface;
use Elephox\DI\Data\TestServiceInterface2;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\DI\ServiceDescriptor
 * @covers \Elephox\DI\InvalidServiceDescriptorException
 * @covers \Elephox\DI\ServiceLifetime
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\DI\ServiceProvider
 *
 * @internal
 */
class ServiceDescriptorTest extends TestCase
{
	public function testCreateInstance(): void
	{
		$instance1 = new TestServiceClass();
		$sd = new ServiceDescriptor(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instance1);

		$sp = new ServiceProvider();
		$instance2 = $sd->createInstance($sp);

		static::assertSame($instance1, $instance2);
	}

	public function testEitherFactoryOrInstanceMustBeSet(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Either factory or instance must be set.');

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
		$this->expectExceptionMessage('Transient/scoped services must have a factory set.');

		new ServiceDescriptor(
			stdClass::class,
			stdClass::class,
			ServiceLifetime::Transient,
			null,
			new TestServiceClass(),
		);
	}

	public function testScopedServiceRequiresFactory(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Transient/scoped services must have a factory set.');

		new ServiceDescriptor(
			stdClass::class,
			stdClass::class,
			ServiceLifetime::Scoped,
			null,
			new TestServiceClass(),
		);
	}

	public function testInstanceMustBeOfImplementationType(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Instance must be of given implementation type (' . stdClass::class . '). Given instance is of type ' . TestServiceClass::class . '.');

		$sd = new ServiceDescriptor(
			stdClass::class,
			stdClass::class,
			ServiceLifetime::Singleton,
			null,
			new TestServiceClass(),
		);

		$sp = new ServiceProvider();
		$sd->createInstance($sp);
	}

	public function testInstanceMustBeOfServiceType(): void
	{
		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Instance must be of given service type (' . stdClass::class . '). Given instance is of type ' . TestServiceClass::class . '.');

		$sd = new ServiceDescriptor(
			stdClass::class,
			TestServiceClass::class,
			ServiceLifetime::Singleton,
			null,
			new TestServiceClass(),
		);

		$sp = new ServiceProvider();
		$sd->createInstance($sp);
	}

	/**
	 * Check that, if a union type is given to the ServiceDescriptor constructor, the instance implements all types included in the union type
	 *
	 * @return void
	 */
	public function testInstanceMustImplementAllServiceTypes(): void
	{
		$sd = new ServiceDescriptor(
			TestServiceInterface::class . '&' . TestServiceInterface2::class,
			TestServiceClass2::class,
			ServiceLifetime::Singleton,
			null,
			new TestServiceClass2(),
		);

		$sp = new ServiceProvider();
		$instance = $sd->createInstance($sp);

		static::assertInstanceOf(TestServiceInterface::class, $instance);
		static::assertInstanceOf(TestServiceInterface2::class, $instance);

		$sd2 = new ServiceDescriptor(
			TestServiceInterface::class . '&' . TestServiceInterface2::class,
			TestServiceClass::class,
			ServiceLifetime::Singleton,
			null,
			new TestServiceClass(),
		);

		$this->expectException(InvalidServiceDescriptorException::class);
		$this->expectExceptionMessage('Instance must be an intersection of all service types (' . TestServiceInterface::class . '&' . TestServiceInterface2::class . '), but the type ' . TestServiceClass::class . ' is missing the ' . TestServiceInterface2::class . ' type.');

		$sd2->createInstance($sp);
	}
}
