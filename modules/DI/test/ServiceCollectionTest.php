<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\DI\ServiceCollection
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\DI\ServiceDescriptor
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 * @uses \Elephox\DI\ServiceResolver
 */
class ServiceCollectionTest extends TestCase
{
	public function testSelfRegister(): void
	{
		$container = new ServiceCollection();

		self::assertTrue($container->has(Contract\ServiceCollection::class));
		self::assertTrue($container->has(Contract\Resolver::class));
	}

	public function testDescribeSingletonInstance(): void
	{
		$container = new ServiceCollection();
		$instance = new TestServiceClass();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instance);

		self::assertTrue($container->has(TestServiceInterface::class));
		self::assertTrue($container->has(TestServiceClass::class));
		self::assertSame($instance, $container->get(TestServiceInterface::class));
		self::assertSame($instance, $container->get(TestServiceClass::class));
	}

	public function testDescribeSingletonFactory(): void
	{
		$container = new ServiceCollection();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, fn () => new TestServiceClass());

		self::assertTrue($container->has(TestServiceInterface::class));
		self::assertTrue($container->has(TestServiceClass::class));
		$a = $container->get(TestServiceInterface::class);
		$b = $container->get(TestServiceInterface::class);
		$c = $container->get(TestServiceClass::class);
		self::assertSame($a, $b);
		self::assertSame($a, $c);
	}

	public function testDescribeTransientFactory(): void
	{
		$container = new ServiceCollection();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Transient, fn () => new TestServiceClass());

		self::assertTrue($container->has(TestServiceInterface::class));
		self::assertTrue($container->has(TestServiceClass::class));
		$a = $container->get(TestServiceInterface::class);
		$b = $container->get(TestServiceInterface::class);
		$c = $container->get(TestServiceClass::class);
		self::assertNotSame($a, $b);
		self::assertNotSame($a, $c);
		self::assertNotSame($b, $c);
	}

	public function testAlias(): void
	{
		$container = new ServiceCollection();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, fn () => new TestServiceClass());
		$container->setAlias('test', TestServiceClass::class);

		self::assertTrue($container->has(TestServiceInterface::class));
		self::assertTrue($container->has(TestServiceClass::class));
		self::assertTrue($container->has('test'));
		self::assertSame($container->get('test'), $container->get(TestServiceClass::class));
		self::assertSame($container->get('test'), $container->get(TestServiceInterface::class));
	}

	public function testDescribeServiceAgain(): void
	{
		$container = new ServiceCollection();
		$instanceA = new TestServiceClass();
		$instanceB = new TestServiceClass();
		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceA);

		self::assertTrue($container->has(TestServiceInterface::class));
		self::assertTrue($container->has(TestServiceClass::class));
		$a = $container->get(TestServiceInterface::class);
		$b = $container->get(TestServiceClass::class);

		self::assertSame($instanceA, $a);
		self::assertSame($instanceA, $b);

		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceB);
		self::assertTrue($container->has(TestServiceInterface::class));
		self::assertTrue($container->has(TestServiceClass::class));
		$c = $container->get(TestServiceInterface::class);
		$d = $container->get(TestServiceClass::class);

		self::assertSame($instanceA, $c);
		self::assertSame($instanceA, $d);

		$container->describe(TestServiceInterface::class, TestServiceClass::class, ServiceLifetime::Singleton, null, $instanceB, true);
		self::assertTrue($container->has(TestServiceInterface::class));
		self::assertTrue($container->has(TestServiceClass::class));
		$e = $container->get(TestServiceInterface::class);
		$f = $container->get(TestServiceClass::class);

		self::assertSame($instanceB, $e);
		self::assertSame($instanceB, $f);
	}
}
