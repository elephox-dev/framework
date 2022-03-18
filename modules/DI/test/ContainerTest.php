<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Contract\Container as ContainerContract;
use Elephox\DI\Contract\NotContainerSerializable;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

/**
 * @covers \Elephox\DI\Container
 * @covers \Elephox\DI\ServiceLifetime
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\DI\Binding
 * @covers \Elephox\DI\InvalidBindingInstanceException
 * @covers \Elephox\DI\UnresolvedParameterException
 * @covers \Elephox\DI\BindingException
 * @covers \Elephox\DI\MissingTypeHintException
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 */
class ContainerTest extends TestCase
{
	public function testConstructor(): void
	{
		$container = new Container();

		self::assertInstanceOf(Container::class, $container);
		self::assertTrue($container->has(ContainerContract::class));
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

		$container->register(ContainerTestInterface::class, ContainerTestClass::class, ServiceLifetime::Transient);

		$instanceA = $container->get(ContainerTestInterface::class);
		$instanceB = $container->get(ContainerTestInterface::class);

		self::assertNotSame($instanceA, $instanceB);
	}

	public function testStoreClassNameWithConstructor(): void
	{
		$container = new Container();

		$testClassInstance = new ContainerTestClass();
		$this->expectWarning();
		$container->register(ContainerTestInterface::class, $testClassInstance, ServiceLifetime::Transient);
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
		$container->register(ContainerTestInterface::class, static fn() => new stdClass(), ServiceLifetime::Transient);

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

	public function testNoConstructorTypesButDefaults(): void
	{
		$container = new Container();
		$container->register(ContainerTestClassMultiParameterConstructorNoTypeButDefault::class);

		$instance = $container->get(ContainerTestClassMultiParameterConstructorNoTypeButDefault::class);
		self::assertNull($instance->testInterface);
		self::assertNull($instance->testInterface2);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testCall(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		self::assertInstanceOf(ContainerTestInterface::class, $container->call(ContainerTestInterface::class, 'method'));
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testCallWithInstance(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->get(ContainerTestInterface::class);

		self::assertInstanceOf(ContainerTestInterface::class, $container->call($instance, 'method'));
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testCallback(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);
		$container->register(ContainerTestClass::class, ContainerTestClass::class);

		self::assertInstanceOf(ContainerTestInterface::class, $container->callback(fn(ContainerTestClass $class, ContainerTestInterface $interface) => $class->method($interface)));
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testWithOverrideArguments(): void
	{
		$container = new Container();
		$interface = new ContainerTestClass();

		$container->register(ContainerTestClassWithConstructor::class, ContainerTestClassWithConstructor::class);
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->instantiate(ContainerTestClassWithConstructor::class, ['testInterface' => $interface]);

		self::assertSame($interface, $instance->testInterface);
	}

	/**
	 * @throws \ReflectionException
	 */
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

	/**
	 * @throws \ReflectionException
	 */
	public function testInstantiateUnionType(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->instantiate(ContainerTestClassUnionParameterType::class);

		self::assertInstanceOf(ContainerTestInterface::class, $instance->testInterface);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testInstantiateInvalidClass(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$container = new Container();
		$container->instantiate(ContainerTestInterface::class);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testRestore(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->restore(ContainerTestClassWithConstructor::class);

		self::assertInstanceOf(ContainerTestClassWithConstructor::class, $instance);
		self::assertInstanceOf(ContainerTestInterface::class, $instance->testInterface);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testRestoreWithOverrideArguments(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->restore(ContainerTestModel::class, ['interface' => $container->get(ContainerTestInterface::class)]);

		self::assertInstanceOf(ContainerTestModel::class, $instance);
		self::assertInstanceOf(ContainerTestInterface::class, $instance->interface);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testRestoreWithClassProperty(): void
	{
		$container = new Container();
		$instance = $container->restore(ContainerTestModel::class);
		self::assertNull($instance->interface);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testInvalidRestore(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$container = new Container();
		$container->restore(ContainerTestInterface::class);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testNameResolvingIsPreferred(): void
	{
		$container = new Container();

		$testInterfaceInstance = new ContainerTestClass();
		$otherTestInterfaceInstance = new ContainerTestClass();

		$container->register(ContainerTestInterface3::class, $testInterfaceInstance, alias: 'testInterface');
		$container->register(ContainerTestInterface::class, $otherTestInterfaceInstance);

		$instance = $container->instantiate(ContainerTestClassMultiParameterConstructorSameType::class);

		self::assertSame($testInterfaceInstance, $instance->testInterface);
		self::assertSame($otherTestInterfaceInstance, $instance->testInterface2);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testNameResolvingIsPreferredInvalidType(): void
	{
		$container = new Container();

		$testInterfaceInstance = new ContainerTestClass2();
		$otherTestInterfaceInstance = new ContainerTestClass2();

		$container->register(ContainerTestInterface2::class, $testInterfaceInstance, alias: 'testInterface');
		$container->register(ContainerTestInterface2::class, $otherTestInterfaceInstance);

		$this->expectException(LogicException::class);

		$container->instantiate(ContainerTestClassMultiParameterConstructorSameType::class);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testNameResolvingIsPreferredNoType(): void
	{
		$container = new Container();

		$testInterfaceInstance = new ContainerTestClass();
		$testInterfaceInstance2 = new ContainerTestClass();

		$container->register(ContainerTestInterface::class, $testInterfaceInstance, alias: 'testInterface');
		$container->register(ContainerTestInterface2::class, $testInterfaceInstance2, alias: 'testInterface2');

		$instance = $container->instantiate(ContainerTestClassMultiParameterConstructorNoType::class);

		self::assertSame($testInterfaceInstance, $instance->testInterface);
		self::assertSame($testInterfaceInstance2, $instance->testInterface2);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testGetOrInstantiate(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);

		$instance = $container->getOrInstantiate(ContainerTestInterface::class);
		$instance2 = $container->getOrInstantiate(ContainerTestClass2::class);

		self::assertInstanceOf(ContainerTestClass::class, $instance);
		self::assertInstanceOf(ContainerTestClass2::class, $instance2);
	}

	public function testGetOrRegister(): void
	{
		$container = new Container();
		$instance = new ContainerTestClass();
		$container->register(ContainerTestClass::class, $instance);

		$instance2 = $container->getOrRegister(ContainerTestClass::class);
		$instance3 = $container->getOrRegister(ContainerTestClass2::class);

		self::assertSame($instance, $instance2);
		self::assertInstanceOf(ContainerTestClass2::class, $instance3);
		self::assertTrue($container->has(ContainerTestClass2::class));
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testSerialization(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);
		$instance = $container->instantiate(ContainerTestSerializable::class);
		$container->register(ContainerTestInterface2::class, $instance);

		$serialized = serialize($container);
		$unserialized = unserialize($serialized);

		self::assertInstanceOf(Container::class, $unserialized);
		self::assertTrue($unserialized->has(ContainerTestInterface::class));
		self::assertTrue($unserialized->has(ContainerTestInterface2::class));
		self::assertTrue($unserialized->has(ContainerContract::class));
		self::assertTrue($unserialized->has(Container::class));

		$class = $unserialized->get(ContainerTestInterface::class);
		self::assertInstanceOf(ContainerTestClass::class, $class);

		$class2 = $unserialized->get(ContainerTestInterface2::class);
		self::assertInstanceOf(ContainerTestSerializable::class, $class2);
		self::assertNotSame($instance, $class2);

		$serializedArray = $container->__serialize();
		$r = new ReflectionClass(Container::class);
		$unserializedArray = $r->newInstanceWithoutConstructor();
		$unserializedArray->__unserialize($serializedArray);

		self::assertInstanceOf(Container::class, $unserializedArray);
		self::assertTrue($unserializedArray->has(ContainerTestInterface::class));
		self::assertTrue($unserialized->has(ContainerTestInterface2::class));
		self::assertTrue($unserializedArray->has(ContainerContract::class));
		self::assertTrue($unserializedArray->has(Container::class));

		$class3 = $unserializedArray->get(ContainerTestInterface::class);
		self::assertInstanceOf(ContainerTestClass::class, $class3);

		$class4 = $unserialized->get(ContainerTestInterface2::class);
		self::assertInstanceOf(ContainerTestSerializable::class, $class4);
		self::assertNotSame($instance, $class4);
	}

	public function testTransientWithSingleton(): void
	{
		$instance = new ContainerTestClass();
		$container = new Container();

		$this->expectWarning();
		$this->expectWarningMessage("Instance lifetime 'Transient' may not have the desired effect when using an object as the implementation. Consider using a callable instead.");
		$container->transient(ContainerTestClass::class, $instance);
	}

	public function testDoesntSerializeNotContainerSerializable(): void
	{
		$container = new Container();
		$container->register(ContainerTestInterface::class, ContainerTestClass::class);
		$container->register(ContainerTestInterface2::class, ContainerTestNotSerializable::class);

		$serialized = unserialize(serialize($container));

		self::assertTrue($serialized->has(ContainerTestInterface2::class));

		$container->get(ContainerTestInterface2::class);

		$serializedAfterInstantiation = unserialize(serialize($container));

		self::assertFalse($serializedAfterInstantiation->has(ContainerTestInterface2::class));
	}
}

interface ContainerTestInterface
{
}

interface ContainerTestInterface2
{
}

interface ContainerTestInterface3 extends ContainerTestInterface
{
}

class ContainerTestClass implements ContainerTestInterface, ContainerTestInterface2, ContainerTestInterface3
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

class ContainerTestClass2 implements ContainerTestInterface2 {
}

class ContainerTestClassWithConstructor
{
	public function __construct(public ContainerTestInterface $testInterface)
	{
	}
}

class ContainerTestClassWithoutConstructorTypes
{
	public function __construct($someVariable)
	{
	}
}

class ContainerTestClassMultiParameterConstructor
{
	public function __construct(
		public ContainerTestInterface $testInterface,
		public ContainerTestInterface2 $testInterface2
	) {
	}
}

class ContainerTestClassMultiParameterConstructorSameType
{

	public function __construct(public ContainerTestInterface $testInterface, public ContainerTestInterface $testInterface2)
	{
	}
}

class ContainerTestClassMultiParameterConstructorNoType
{

	public function __construct(public $testInterface, public $testInterface2)
	{
	}
}

class ContainerTestClassMultiParameterConstructorNoTypeButDefault
{

	public function __construct(public $testInterface = null, public $testInterface2 = null)
	{
	}
}

class ContainerTestClassMultiParameterConstructorOptional implements ContainerTestInterface
{

	public function __construct(public ContainerTestInterface $testInterface, public ?ContainerTestInterface2 $testInterface2 = null)
	{
	}
}

class ContainerTestClassMultiParameterConstructorNullable implements ContainerTestInterface
{

	public function __construct(public ContainerTestInterface $testInterface, public ?ContainerTestInterface2 $testInterface2)
	{
	}
}

class ContainerTestClassUnionParameterType implements ContainerTestInterface
{

	public function __construct(public ContainerTestInterface|ContainerTestInterface2 $testInterface)
	{
	}
}

class ContainerTestClassUnionParameterTypeNullable implements ContainerTestInterface
{

	public function __construct(public ContainerTestInterface|ContainerTestInterface2|null $testInterface = null)
	{
	}
}

class ContainerTestModel
{
	public ?ContainerTestInterface $interface = null;
}

class ContainerTestSerializable implements ContainerTestInterface2 {
	public function __construct(public ContainerTestInterface $testInterface)
	{
	}

	public function __serialize(): array
	{
		return [
			'interface' => $this->testInterface
		];
	}

	public function __unserialize(array $data): void
	{
		$this->testInterface = $data['interface'];
	}
}

class ContainerTestNotSerializable implements ContainerTestInterface2, NotContainerSerializable {
	public function __construct(public ContainerTestInterface $testInterface)
	{
	}

	public function __serialize(): array
	{
		return [
			'interface' => $this->testInterface
		];
	}

	public function __unserialize(array $data): void
	{
		$this->testInterface = $data['interface'];
	}
}
