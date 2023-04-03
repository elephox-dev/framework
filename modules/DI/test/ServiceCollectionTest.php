<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadMethodCallException;
use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceClass2;
use Elephox\DI\Data\TestServiceClass3;
use Elephox\DI\Data\TestServiceClassUninstantiable;
use Elephox\DI\Data\TestServiceClassWithConstructor;
use Elephox\DI\Data\TestServiceInterface;
use Elephox\DI\Data\TestServiceInterface2;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * @covers \Elephox\DI\ServiceCollection
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\DI\ServiceDescriptor
 * @covers \Elephox\DI\Hooks\ServiceDescriptorHookData
 * @covers \Elephox\DI\Hooks\ServiceHookData
 * @covers \Elephox\DI\Hooks\ServiceResolvedHookData
 * @covers \Elephox\DI\Hooks\AliasHookData
 * @covers \Elephox\DI\Hooks\ServiceReplacedHookData
 * @covers \Elephox\DI\ServiceNotFoundException
 * @covers \Elephox\DI\UnresolvedParameterException
 * @covers \Elephox\DI\ClassNotFoundException
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\DI\ServiceLifetime
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 * @uses \Elephox\DI\ServiceResolver
 *
 * @internal
 */
class ServiceCollectionTest extends MockeryTestCase
{
	public function testSelfRegister(): void
	{
		$container = new ServiceCollection();

		static::assertTrue($container->has(Contract\ServiceCollection::class));
		static::assertTrue($container->has(Contract\Resolver::class));
	}

	public function testDescribeSingletonInstance(): void
	{
		$container = new ServiceCollection();
		$instance = new TestServiceClass();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instance);

		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));
		static::assertSame($instance, $container->get(TestServiceInterface::class));
		static::assertSame($instance, $container->get(TestServiceClass::class));
	}

	public function testDescribeSingletonFactory(): void
	{
		$container = new ServiceCollection();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, static fn () => new TestServiceClass());

		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));
		$a = $container->get(TestServiceInterface::class);
		$b = $container->get(TestServiceInterface::class);
		$c = $container->get(TestServiceClass::class);
		static::assertSame($a, $b);
		static::assertSame($a, $c);
	}

	public function testDescribeTransientFactory(): void
	{
		$container = new ServiceCollection();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Transient, static fn () => new TestServiceClass());

		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));
		$a = $container->get(TestServiceInterface::class);
		$b = $container->get(TestServiceInterface::class);
		$c = $container->get(TestServiceClass::class);
		static::assertNotSame($a, $b);
		static::assertNotSame($a, $c);
		static::assertNotSame($b, $c);
	}

	public function testAlias(): void
	{
		$container = new ServiceCollection();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, static fn () => new TestServiceClass());
		$container->setAlias('test', TestServiceClass::class);

		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));
		static::assertTrue($container->has('test'));
		static::assertSame($container->get('test'), $container->get(TestServiceClass::class));
		static::assertSame($container->get('test'), $container->get(TestServiceInterface::class));
	}

	public function testDescribeServiceAgain(): void
	{
		$container = new ServiceCollection();
		$instanceA = new TestServiceClass();
		$instanceB = new TestServiceClass();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceA);

		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));
		$a = $container->get(TestServiceInterface::class);
		$b = $container->get(TestServiceClass::class);

		static::assertSame($instanceA, $a);
		static::assertSame($instanceA, $b);

		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceB);
		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));
		$c = $container->get(TestServiceInterface::class);
		$d = $container->get(TestServiceClass::class);

		static::assertSame($instanceA, $c);
		static::assertSame($instanceA, $d);

		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceB, true);
		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));
		$e = $container->get(TestServiceInterface::class);
		$f = $container->get(TestServiceClass::class);

		static::assertSame($instanceB, $e);
		static::assertSame($instanceB, $f);
	}

	public function testRemove(): void
	{
		$container = new ServiceCollection();
		$instanceA = new TestServiceClass();

		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceA);

		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));

		$container->remove(TestServiceInterface::class);

		static::assertFalse($container->has(TestServiceInterface::class));
		static::assertFalse($container->has(TestServiceClass::class));

		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceA);

		static::assertTrue($container->has(TestServiceInterface::class));
		static::assertTrue($container->has(TestServiceClass::class));

		$container->remove(TestServiceClass::class);

		static::assertFalse($container->has(TestServiceInterface::class));
		static::assertFalse($container->has(TestServiceClass::class));
	}

	public function testServiceResolverInstantiateThrowsForUnknownClass(): void
	{
		$resolver = new ServiceCollection();

		$this->expectException(ClassNotFoundException::class);
		$this->expectExceptionMessage('Class not found: IDontExistClass');

		$resolver->instantiate('IDontExistClass');
	}

	public function testServiceResolverInstantiateThrowsForReflectionException(): void
	{
		$resolver = new ServiceCollection();

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage("Failed to instantiate class 'Elephox\DI\Data\TestServiceClassUninstantiable'");

		$resolver->instantiate(TestServiceClassUninstantiable::class);
	}

	public function testServiceResolverInstantiateNoConstructor(): void
	{
		$resolver = new ServiceCollection();

		$instance = $resolver->instantiate(TestServiceClass::class);

		static::assertInstanceOf(TestServiceClass::class, $instance);
	}

	public function testServiceResolverInstantiateWithConstructor(): void
	{
		$resolver = new ServiceCollection();
		$testServiceClass = new TestServiceClass();
		$resolver->addSingleton(TestServiceInterface::class, instance: $testServiceClass);

		$instance = $resolver->instantiate(TestServiceClassWithConstructor::class);

		static::assertInstanceOf(TestServiceClassWithConstructor::class, $instance);
		static::assertSame($testServiceClass, $instance->testService);
	}

	public function testServiceResolverCall(): void
	{
		$resolver = new ServiceCollection();
		$resolver->addTransient(TestServiceClass::class, TestServiceClass::class);

		$result = $resolver->call(TestServiceClass::class, 'returnsString', ['testString' => 'This is a test string']);

		static::assertSame('This is a test string', $result);
	}

	public function testServiceResolverCallThrowsForNonExistentMethod(): void
	{
		$resolver = new ServiceCollection();
		$resolver->addTransient(TestServiceClass::class, TestServiceClass::class);

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage("Failed to call method 'doesntExist' on class 'Elephox\DI\Data\TestServiceClass'");

		$resolver->call(TestServiceClass::class, 'doesntExist');
	}

	public function testServiceResolverCallOn(): void
	{
		$resolver = new ServiceCollection();
		$inst = new TestServiceClass();

		$result = $resolver->callOn($inst, 'returnsString', ['testString' => 'This is a test string']);

		static::assertSame('This is a test string', $result);
	}

	public function testServiceResolverCallThrowsForReflectionException(): void
	{
		$resolver = new ServiceCollection();
		$resolver->addTransient(TestServiceClassUninstantiable::class, TestServiceClassUninstantiable::class);

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage("Failed to instantiate class 'Elephox\DI\Data\TestServiceClassUninstantiable'");

		$resolver->call(TestServiceClassUninstantiable::class, 'returnsString', ['testString' => 'This is a test string']);
	}

	public function testServiceResolverCallStatic(): void
	{
		$resolver = new ServiceCollection();
		$resolver->addTransient(TestServiceClass::class, TestServiceClass::class);

		$result = $resolver->callStatic(TestServiceClass::class, 'returnsStringStatic', ['testString' => 'This is a test string']);

		static::assertSame('This is a test string', $result);
	}

	public function testServiceResolverCallStaticThrowsForNonExistentMethod(): void
	{
		$resolver = new ServiceCollection();
		$resolver->addTransient(TestServiceClass::class, TestServiceClass::class);

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage("Failed to call method 'doesntExist' on class 'Elephox\DI\Data\TestServiceClass'");

		$resolver->callStatic(TestServiceClass::class, 'doesntExist');
	}

	public function testServiceResolverCallbackStaticClosure(): void
	{
		$resolver = new ServiceCollection();
		$resolver->addTransient(TestServiceClass::class, TestServiceClass::class);

		$closure = static fn (TestServiceClass $t) => $t->returnsString('test');

		$result = $resolver->callback($closure);

		static::assertSame('test', $result);
	}

	public function testRequireLate(): void
	{
		$serviceCollection = new ServiceCollection();
		$service = new TestServiceClass();
		$serviceCollection->addSingleton(TestServiceClass::class, instance: $service);

		$callback = $serviceCollection->requireLate(TestServiceClass::class);
		$returned = $callback();

		static::assertSame($returned, $service);

		$callbackService = $serviceCollection->requireServiceLate(TestServiceClass::class);
		$returnedService = $callbackService();

		static::assertSame($returnedService, $service);
	}

	/**
	 * @throws ReflectionException
	 */
	public function testResolveArguments(): void
	{
		$service = new TestServiceClass();
		$reflectionSimple = new ReflectionMethod($service, 'returnsString');
		$reflectionService = new ReflectionMethod($service, 'returnsTestServiceInterface');

		$serviceCollection = new ServiceCollection();

		$simpleArgs = $serviceCollection->resolveArguments($reflectionSimple, ['testString' => 'Hello']);
		static::assertCount(1, $simpleArgs);
		static::assertSame('Hello', $simpleArgs->pop());

		$simpleArgsByIndex = $serviceCollection->resolveArguments($reflectionSimple, ['Hello']);
		static::assertCount(1, $simpleArgsByIndex);
		static::assertSame('Hello', $simpleArgsByIndex->pop());

		$serviceCollection->addSingleton(TestServiceInterface::class, instance: $service);
		$serviceArgs = $serviceCollection->resolveArguments($reflectionService);
		static::assertCount(1, $serviceArgs);
		static::assertSame($service, $serviceArgs->pop());
	}

	/**
	 * @throws ReflectionException
	 */
	public function testResolveUnresolvableArguments(): void
	{
		$service = new TestServiceClass();
		$reflectionService = new ReflectionMethod($service, 'returnsTestServiceInterface');

		$serviceCollection = new ServiceCollection();

		$this->expectException(UnresolvedParameterException::class);
		$this->expectExceptionMessage('Could not resolve parameter $service with type Elephox\DI\Data\TestServiceInterface in TestServiceClass::returnsTestServiceInterface()');
		$serviceCollection->resolveArguments($reflectionService);
	}

	/**
	 * @throws ReflectionException
	 */
	public function testResolveUnresolvableArgumentsWithCallback(): void
	{
		$service = new TestServiceClass();
		$reflectionService = new ReflectionMethod($service, 'returnsTestServiceInterface');

		$serviceCollection = new ServiceCollection();
		$serviceArgs = $serviceCollection->resolveArguments($reflectionService, onUnresolved: static function (ReflectionParameter $parameter) use ($service) {
			static::assertSame('service', $parameter->getName());
			static::assertSame(TestServiceInterface::class, $parameter->getType()?->getName());

			return $service;
		});

		static::assertCount(1, $serviceArgs);
		static::assertSame($service, $serviceArgs->pop());
	}

	public function testScoped(): void
	{
		$collection = new ServiceCollection();

		$collection->addScoped(TestServiceClass::class, TestServiceClass::class, static fn () => new TestServiceClass());
		$collection->addSingleton(TestServiceClass2::class, TestServiceClass2::class, static fn () => new TestServiceClass2());
		$collection->addTransient(TestServiceClass3::class, TestServiceClass3::class, static fn () => new TestServiceClass3());

		static::assertTrue($collection->hasService(TestServiceClass::class));
		static::assertTrue($collection->hasService(TestServiceClass2::class));
		static::assertTrue($collection->hasService(TestServiceClass3::class));

		$inst1 = $collection->requireService(TestServiceClass::class);
		$inst2 = $collection->requireService(TestServiceClass::class);

		static::assertSame($inst1, $inst2);

		$inst3 = $collection->requireService(TestServiceClass2::class);

		$collection->endScope();

		static::assertTrue($collection->hasService(TestServiceClass::class));
		static::assertTrue($collection->hasService(TestServiceClass2::class));
		static::assertTrue($collection->hasService(TestServiceClass3::class));

		$inst5 = $collection->requireService(TestServiceClass::class);
		$inst6 = $collection->requireService(TestServiceClass2::class);

		static::assertNotSame($inst1, $inst5);
		static::assertSame($inst3, $inst6);
	}

	public function testResolveDNFType(): void {
		$collection = new ServiceCollection();

		$instance1 = new TestServiceClass2();

		$collection->addSingleton(TestServiceInterface::class, instance: $instance1);
		$collection->addSingleton(TestServiceInterface2::class, instance: $instance1);

		$result = $collection->callStatic(TestServiceClass3::class, "takesDnfType");

		static::assertNull($result);

		$collection->addSingleton(TestServiceInterface::class . "&" . TestServiceInterface2::class, instance: $instance1);

		$result2 = $collection->callStatic(TestServiceClass3::class, "takesDnfType");

		static::assertSame($result2, $instance1);
	}
}
