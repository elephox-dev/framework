<?php
declare(strict_types=1);

namespace Elephox\DI;

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;
use stdClass;

/**
 * @covers \Elephox\DI\Container
 * @covers \Elephox\DI\InstanceLifetime
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\DI\Binding
 * @covers \Elephox\DI\InvalidBindingInstanceException
 * @covers \Elephox\DI\BindingNotFoundException
 * @covers \Elephox\DI\BindingException
 * @covers \Elephox\DI\MissingTypeHintException
 */
class ContainerTest extends MockeryTestCase
{
	public function testConstructor(): void
	{
		$container = new Container();

		self::assertInstanceOf(Container::class, $container);
		self::assertTrue($container->has(\Elephox\DI\Contract\Container::class));
		self::assertTrue($container->has(Container::class));
	}

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
		$container->transient(ContainerTestInterface::class, $factory);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertNotSame($instanceA, $instanceB);
	}

	public function testStoreFactoryRequest(): void
	{
		$container = new Container();

		$factory = static fn(): ContainerTestInterface => new ContainerTestClass();
		$container->singleton(ContainerTestInterface::class, $factory);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertSame($instanceA, $instanceB);
	}

	public function testStoreClassName(): void
	{
		$container = new Container();

		$container->register(ContainerTestInterface::class, ContainerTestClass::class, InstanceLifetime::Transient);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertNotSame($instanceA, $instanceB);
	}

	public function testStoreClassNameWithConstructor(): void
	{
		$container = new Container();

		$testClassInstance = new ContainerTestClass();
		$container->register(ContainerTestInterface::class, $testClassInstance, InstanceLifetime::Transient);
		$container->transient(ContainerTestClassWithConstructor::class);

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

	public function testStoreClassNameWithConstructorMultiParametersNullable(): void
	{
		$container = new Container();

		$testInterface = new ContainerTestClass();

		$container->register(ContainerTestInterface::class, $testInterface);
		$container->register(ContainerTestClassMultiParameterConstructorNullable::class, ContainerTestClassMultiParameterConstructorNullable::class);

		$instance = $container->get(ContainerTestClassMultiParameterConstructorNullable::class);

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
		$container->register(ContainerTestInterface::class, static fn() => new stdClass());

		$this->expectException(BindingException::class);

		$container->get(ContainerTestInterface::class);
	}

	public function testInvalidBindingTransient(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, static fn() => new stdClass(), InstanceLifetime::Transient);

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

	public function testCall(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		self::assertInstanceOf(ContainerTestInterface::class, $container->call(ContainerTestInterface::class, 'method'));
	}

	public function testCallWithInstance(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->get(ContainerTestInterface::class);

		self::assertInstanceOf(ContainerTestInterface::class, $container->call($instance, 'method'));
	}

	public function testCallback(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);
		$container->register(ContainerTestClass::class, ContainerTestClass::class);

		self::assertInstanceOf(ContainerTestInterface::class, $container->callback(fn(ContainerTestClass $class, ContainerTestInterface $interface) => $class->method($interface)));
	}

	public function testWithOverrideArguments(): void
	{
		$container = new Container();
		$interface = new ContainerTestClass();

		$container->register(ContainerTestClassWithConstructor::class, ContainerTestClassWithConstructor::class);
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->instantiate(ContainerTestClassWithConstructor::class, ['testInterface' => $interface]);

		self::assertSame($interface, $instance->testInterface);
	}

	public function testVariadicCall(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

	    $args = $container->call(ContainerTestInterface::class, 'variadic', ['test', 'test2']);

		self::assertInstanceOf(ContainerTestInterface::class, $args[0]);
		self::assertEquals('test', $args[1]);
		self::assertEquals('test2', $args[2]);
	}

	public function testInterfaceAsClassName(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$container = new Container();
		$container->register(ContainerTestInterface::class);
	}

	public function testGetAlias(): void
	{
		$container = new Container();
		$container->singleton(ContainerTestInterface::class, ContainerTestClass::class, 'test');

		self::assertTrue($container->has('test'));

		$instance = $container->get('test');

		self::assertInstanceOf(ContainerTestInterface::class, $instance);

		$container->alias('test2', 'test');

		self::assertTrue($container->has('test2'));

		$instance2 = $container->get('test2');

		self::assertInstanceOf(ContainerTestInterface::class, $instance);
		self::assertSame($instance, $instance2);
	}
}

interface ContainerTestInterface
{
}

interface ContainerTestInterface2
{
}

class ContainerTestClass implements ContainerTestInterface, ContainerTestInterface2
{
	public function method(ContainerTestInterface $instance): ContainerTestInterface
	{
		return $instance;
	}

	public function variadic(ContainerTestInterface $instance, string ...$args): array
	{
		return [$instance, ...$args];
	}
}

class ContainerTestClassWithConstructor
{
	public ContainerTestInterface $testInterface;

	public function __construct(ContainerTestInterface $testInterface)
	{
		$this->testInterface = $testInterface;
	}
}

class ContainerTestClassWithoutConstructorTypes implements ContainerTestInterface
{
	public function __construct($someVariable)
	{
	}
}

class ContainerTestClassMultiParameterConstructor implements ContainerTestInterface
{
	public ContainerTestInterface $testInterface;
	public ContainerTestInterface2 $testInterface2;

	public function __construct(ContainerTestInterface $testInterface, ContainerTestInterface2 $testInterface2)
	{
		$this->testInterface = $testInterface;
		$this->testInterface2 = $testInterface2;
	}
}

class ContainerTestClassMultiParameterConstructorOptional implements ContainerTestInterface
{
	public ContainerTestInterface $testInterface;
	public ?ContainerTestInterface2 $testInterface2;

	public function __construct(ContainerTestInterface $testInterface, ?ContainerTestInterface2 $testInterface2 = null)
	{
		$this->testInterface = $testInterface;
		$this->testInterface2 = $testInterface2;
	}
}


class ContainerTestClassMultiParameterConstructorNullable implements ContainerTestInterface
{
	public ContainerTestInterface $testInterface;
	public ?ContainerTestInterface2 $testInterface2;

	public function __construct(ContainerTestInterface $testInterface, ?ContainerTestInterface2 $testInterface2)
	{
		$this->testInterface = $testInterface;
		$this->testInterface2 = $testInterface2;
	}
}
