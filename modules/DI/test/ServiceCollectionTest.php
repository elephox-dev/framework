<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceClassWithConstructor;
use Elephox\DI\Data\TestServiceInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;
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

	public function testServiceResolverInstantiateNoConstructor(): void
	{
		$resolver = M::mock(ServiceResolver::class);

		$instance = $resolver->instantiate(TestServiceClass::class);

		static::assertInstanceOf(TestServiceClass::class, $instance);
	}

	public function testServiceResolverInstantiateWithConstructor(): void
	{
		$serviceCollection = M::mock(ServiceCollectionContract::class);
		$resolver = M::mock(ServiceResolver::class);
		$resolver->shouldAllowMockingProtectedMethods();
		$resolver
			->allows('getServices')
			->twice()
			->withNoArgs()
			->andReturn($serviceCollection)
		;

		$serviceCollection
			->expects('get')
			->with('testService')
			->andReturn(null)
		;

		$testServiceClass = new TestServiceClass();
		$serviceCollection
			->expects('requireService')
			->with(TestServiceInterface::class)
			->andReturn($testServiceClass)
		;

		$instance = $resolver->instantiate(TestServiceClassWithConstructor::class);

		static::assertInstanceOf(TestServiceClassWithConstructor::class, $instance);
		static::assertSame($testServiceClass, $instance->testService);
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
		$serviceArgs = $serviceCollection->resolveArguments($reflectionService, onUnresolved: function (ReflectionParameter $parameter) use ($service) {
			static::assertSame('service', $parameter->getName());
			static::assertSame(TestServiceInterface::class, $parameter->getType()?->getName());

			return $service;
		});

		static::assertCount(1, $serviceArgs);
		static::assertSame($service, $serviceArgs->pop());
	}
}
