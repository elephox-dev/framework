<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Data\TestServiceClass;
use Elephox\DI\Data\TestServiceInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\DI\ServiceCollection
 * @covers \Elephox\Collection\ObjectSet
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Iterator\SplObjectStorageIterator
 * @covers \Elephox\DI\ServiceDescriptor
 * @covers \Elephox\DI\ServiceProvider
 *
 * @internal
 */
final class ServiceCollectionTest extends TestCase
{
	public function testCannotAddSingletonWithoutAWayToInstantiateInterface(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Either one of implementationType, implementation or factory must be set if the service is not a class name.');

		$collection = new ServiceCollection();
		$collection->addSingleton(TestServiceInterface::class);
	}

	public function testCanAddSingletonClassWithoutAnyParameters(): void
	{
		$collection = new ServiceCollection();
		$collection->addSingleton(TestServiceClass::class);

		$provider = $collection->buildProvider();
		self::assertTrue($provider->has(TestServiceClass::class));
	}
}
