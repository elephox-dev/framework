<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Elephox\DI\ServiceCollectionOld
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
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 * @uses \Elephox\DI\ServiceResolver
 *
 * @internal
 */
class ServiceProviderTest extends MockeryTestCase
{
	public function testSelfRegister(): void
	{
		$container = new ServiceProvider([]);

		static::assertTrue($container->has(ContainerInterface::class));
		static::assertTrue($container->has(Contract\ServiceProvider::class));
		static::assertTrue($container->has(Contract\RootServiceProvider::class));
		static::assertTrue($container->has(Contract\Resolver::class));
		static::assertTrue($container->has(Contract\ServiceScopeFactory::class));
	}

	public function testSingletonOnlyCreatesOneInstance(): void
	{
		$collection = new ServiceCollection();
		$collection->addSingleton(TestServiceInterface::class, TestServiceClass::class);

		$provider = $collection->buildProvider();
		static::assertTrue($provider->has(TestServiceInterface::class));

		$instance = $provider->require(TestServiceInterface::class);
		static::assertSame($instance, $provider->get(TestServiceInterface::class));
	}
}
