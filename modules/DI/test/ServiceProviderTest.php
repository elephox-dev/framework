<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestDisposableClass;
use Elephox\DI\Data\TestDisposableInterface;
use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\DI\ServiceDescriptor
 * @covers \Elephox\DI\ServiceNotFoundException
 * @covers \Elephox\DI\UnresolvedParameterException
 * @covers \Elephox\DI\ClassNotFoundException
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\DI\ServiceLifetime
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\DI\ServiceProvider
 * @covers \Elephox\DI\ResolverStack
 * @covers \Elephox\DI\ServiceCollection
 * @covers \Elephox\DI\ServiceProvider
 * @covers \Elephox\DI\DynamicResolver
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Iterator\SplObjectStorageIterator
 * @covers \Elephox\Collection\ObjectSet
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class ServiceProviderTest extends MockeryTestCase
{
	public function testSelfRegister(): void
	{
		$container = new ServiceProvider();

		self::assertTrue($container->has(ContainerInterface::class));
		self::assertTrue($container->has(Contract\ServiceProvider::class));
		self::assertTrue($container->has(Contract\Resolver::class));
	}

	public function testSingletonOnlyCreatesOneInstance(): void
	{
		$collection = new ServiceCollection();
		$collection->addSingleton(TestServiceInterface::class, TestServiceClass::class);

		$provider = $collection->buildProvider();
		self::assertTrue($provider->has(TestServiceInterface::class));

		$instance = $provider->get(TestServiceInterface::class);
		self::assertSame($instance, $provider->get(TestServiceInterface::class));
	}

	public function testDisposablesGetDisposedOnDispose(): void
	{
		$collection = new ServiceCollection();
		$collection->addSingleton(TestDisposableInterface::class, TestDisposableClass::class);

		$provider = $collection->buildProvider();
		$provider->get(TestDisposableInterface::class);

		$provider->dispose();
		self::assertSame(1, TestDisposableClass::$disposeCount);

		// calling again has no effect since the instance should have been removed from the provider
		$provider->dispose();
		self::assertSame(1, TestDisposableClass::$disposeCount);

		// creating a new instance should dispose it again
		$provider->get(TestDisposableInterface::class);
		$provider->dispose();
		self::assertSame(2, TestDisposableClass::$disposeCount);
	}
}
