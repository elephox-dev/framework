<?php
declare(strict_types=1);

namespace Elephox\DI;

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
		$container = new ServiceProvider([]);

		self::assertTrue($container->has(ContainerInterface::class));
		self::assertTrue($container->has(Contract\ServiceProvider::class));
		self::assertTrue($container->has(Contract\RootServiceProvider::class));
		self::assertTrue($container->has(Contract\Resolver::class));
		self::assertTrue($container->has(Contract\ServiceScopeFactory::class));
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
}
