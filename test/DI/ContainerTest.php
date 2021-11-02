<?php

namespace Philly\DI;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Philly\DI\Container
 * @covers \Philly\DI\InjectionLifetime
 * @covers \Philly\Collection\HashMap
 * @covers \Philly\DI\Binding
 */
class ContainerTest extends MockeryTestCase
{
	/** @noinspection PhpUnhandledExceptionInspection */
	public function testStoreInstance(): void
	{
		$container = new Container();

		$instance = new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $instance);

		self::assertSame($instance, $container->get(ContainerTestInterface::class));
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testStoreFactory(): void
	{
		$container = new Container();

		$factory = static fn(): ContainerTestInterface => new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $factory, InjectionLifetime::Transient);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertNotSame($instanceA, $instanceB);
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testStoreClassName(): void
	{
		$container = new Container();

		$container->register(ContainerTestInterface::class, ContainerTestClass::class, InjectionLifetime::Transient);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertNotSame($instanceA, $instanceB);
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function testStoreClassNameWithConstructor(): void
	{
		$container = new Container();

		$testClassInstance = new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $testClassInstance, InjectionLifetime::Transient);
		$container->register(ContainerTestClassWithConstructor::class, ContainerTestClassWithConstructor::class, InjectionLifetime::Transient);

		$instance = $container->get(ContainerTestClassWithConstructor::class);

		self::assertSame($testClassInstance, $instance->testInterface);
	}
}

interface ContainerTestInterface{}
class ContainerTestClass implements ContainerTestInterface {}
class ContainerTestClassWithConstructor {
	public ContainerTestInterface $testInterface;

	public function __construct(ContainerTestInterface $testInterface)
	{
		$this->testInterface = $testInterface;
	}
}
