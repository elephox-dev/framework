<?php
declare(strict_types=1);

namespace Philly\DI;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;
use stdClass;

/**
 * @covers \Philly\DI\Container
 * @covers \Philly\DI\BindingLifetime
 * @covers \Philly\Collection\ArrayMap
 * @covers \Philly\Collection\ArrayList
 * @covers \Philly\DI\Binding
 * @covers \Philly\DI\InvalidBindingInstanceException
 * @covers \Philly\DI\BindingNotFoundException
 * @covers \Philly\DI\BindingException
 * @covers \Philly\DI\MissingTypeHintException
 */
class ContainerTest extends MockeryTestCase
{
	public function testStoreInstance(): void
	{
		$container = new Container();

		$instance = new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $instance);

		self::assertSame($instance, $container->get(ContainerTestInterface::class));
	}

	public function testStoreFactory(): void
	{
		$container = new Container();

		$factory = static fn(): ContainerTestInterface => new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $factory, BindingLifetime::Transient);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertNotSame($instanceA, $instanceB);
	}

	public function testStoreFactoryRequest(): void
	{
		$container = new Container();

		$factory = static fn(): ContainerTestInterface => new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $factory);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertSame($instanceA, $instanceB);
	}

	public function testStoreClassName(): void
	{
		$container = new Container();

		$container->register(ContainerTestInterface::class, ContainerTestClass::class, BindingLifetime::Transient);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertNotSame($instanceA, $instanceB);
	}

	public function testStoreClassNameWithConstructor(): void
	{
		$container = new Container();

		$testClassInstance = new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $testClassInstance, BindingLifetime::Transient);
		$container->register(ContainerTestClassWithConstructor::class, ContainerTestClassWithConstructor::class, BindingLifetime::Transient);

		$instance = $container->get(ContainerTestClassWithConstructor::class);
		$instance2 = $container->get(ContainerTestClassWithConstructor::class);

		self::assertSame($testClassInstance, $instance->testInterface);
		self::assertSame($testClassInstance, $instance2->testInterface);
		self::assertNotSame($instance, $instance2);
	}

	public function testStoreClassNameWithConstructorMultiParameters(): void
	{
		$container = new Container();

		$testInterface = new ContainerTestClass();
		$testInterface2 = new ContainerTestClass();

		$container->register(ContainerTestInterface::class, $testInterface);
		$container->register(ContainerTestInterface2::class, $testInterface2);
		$container->register(ContainerTestClassMultiParameterConstructor::class, ContainerTestClassMultiParameterConstructor::class);

		$instance = $container->get(ContainerTestClassMultiParameterConstructor::class);

		self::assertSame($testInterface, $instance->testInterface);
		self::assertSame($testInterface2, $instance->testInterface2);
	}

	public function testStoreClassNameWithConstructorMultiParametersNonOptional(): void
	{
		$container = new Container();

		$testInterface = new ContainerTestClass();

		$container->register(ContainerTestInterface::class, $testInterface);
		$container->register(ContainerTestClassMultiParameterConstructor::class, ContainerTestClassMultiParameterConstructor::class);

		$this->expectException(BindingException::class);

		$container->get(ContainerTestClassMultiParameterConstructor::class);
	}

	public function testStoreClassNameWithConstructorMultiParametersOptional(): void
	{
		$container = new Container();

		$testInterface = new ContainerTestClass();

		$container->register(ContainerTestInterface::class, $testInterface);
		$container->register(ContainerTestClassMultiParameterConstructorOptional::class, ContainerTestClassMultiParameterConstructorOptional::class);

		$instance = $container->get(ContainerTestClassMultiParameterConstructorOptional::class);

		self::assertSame($testInterface, $instance->testInterface);
		self::assertNull($instance->testInterface2);
	}

	public function testNotRegistered(): void
	{
		$container = new Container();

		$this->expectException(RuntimeException::class);

		$container->get(ContainerTestInterface::class);
	}

	public function testInvalidBindingRequest(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, static fn () => new stdClass());

		$this->expectException(BindingException::class);

		$container->get(ContainerTestInterface::class);
	}

	public function testInvalidBindingTransient(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, static fn() => new stdClass(), BindingLifetime::Transient);

		$this->expectException(BindingException::class);

		$container->get(ContainerTestInterface::class);
	}

	public function testNoConstructorTypes(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClassWithoutConstructorTypes::class);

		$this->expectException(RuntimeException::class);

		$container->get(ContainerTestInterface::class);
	}
}

interface ContainerTestInterface{}
interface ContainerTestInterface2{}
class ContainerTestClass implements ContainerTestInterface, ContainerTestInterface2 {}
class ContainerTestClassWithConstructor {
	public ContainerTestInterface $testInterface;

	public function __construct(ContainerTestInterface $testInterface)
	{
		$this->testInterface = $testInterface;
	}
}
class ContainerTestClassWithoutConstructorTypes implements ContainerTestInterface {
	public function __construct($someVariable)
	{
	}
}
class ContainerTestClassMultiParameterConstructor implements ContainerTestInterface {
	public ContainerTestInterface $testInterface;
	public ContainerTestInterface2 $testInterface2;
	public function __construct(ContainerTestInterface $testInterface, ContainerTestInterface2 $testInterface2)
	{
		$this->testInterface = $testInterface;
		$this->testInterface2 = $testInterface2;
	}
}
class ContainerTestClassMultiParameterConstructorOptional implements ContainerTestInterface {
	public ContainerTestInterface $testInterface;
	public ?ContainerTestInterface2 $testInterface2;
	public function __construct(ContainerTestInterface $testInterface, ?ContainerTestInterface2 $testInterface2 = null)
	{
		$this->testInterface = $testInterface;
		$this->testInterface2 = $testInterface2;
	}
}
