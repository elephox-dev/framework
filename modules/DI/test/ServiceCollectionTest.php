<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceClassWithConstructor;
use Elephox\DI\Data\TestServiceInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

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
		$serviceCollection = M::mock(\Elephox\DI\Contract\ServiceCollection::class);
		$resolver = M::mock(ServiceResolver::class)->shouldAllowMockingProtectedMethods();
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
}
