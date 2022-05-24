<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Hooks\AliasHookData;
use Elephox\DI\Hooks\Contract\AliasAddedHook;
use Elephox\DI\Hooks\Contract\AliasRemovedHook;
use Elephox\DI\Hooks\Contract\ServiceAddedHook;
use Elephox\DI\Hooks\Contract\ServiceRemovedHook;
use Elephox\DI\Hooks\Contract\ServiceReplacedHook;
use Elephox\DI\Hooks\Contract\ServiceRequestedHook;
use Elephox\DI\Hooks\Contract\ServiceResolvedHook;
use Elephox\DI\Hooks\Contract\UnknownAliasRequestedHook;
use Elephox\DI\Hooks\Contract\UnknownServiceRequestedHook;
use Elephox\DI\Hooks\ServiceDescriptorHookData;
use Elephox\DI\Hooks\ServiceHookData;
use Elephox\DI\Hooks\ServiceReplacedHookData;
use Elephox\DI\Hooks\ServiceResolvedHookData;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\DI\ServiceCollection
 * @covers \Elephox\DI\Hooks\AliasHookData
 * @covers \Elephox\DI\Hooks\ServiceDescriptorHookData
 * @covers \Elephox\DI\Hooks\ServiceHookData
 * @covers \Elephox\DI\Hooks\ServiceReplacedHookData
 * @covers \Elephox\DI\Hooks\ServiceResolvedHookData
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\IsArrayEnumerable
 * @covers \Elephox\DI\ServiceDescriptor
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\DI\ServiceNotFoundException
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\OffsetNotFoundException
 *
 * @internal
 */
class ServiceCollectionHooksTest extends TestCase
{
	private ?ServiceCollection $serviceCollection = null;

	protected function setUp(): void
	{
		$this->serviceCollection = new ServiceCollection();
	}

	public function testAliasAddedHook(): void
	{
		$storage = new AliasAddedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$this->serviceCollection?->setAlias('foo', 'bar');

		static::assertNotNull($storage->data);
		static::assertSame('foo', $storage->data->alias);
		static::assertSame('bar', $storage->data->serviceName);
	}

	public function testAliasRemovedHook(): void
	{
		$storage = new AliasRemovedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$this->serviceCollection?->setAlias('foo', 'bar');
		$this->serviceCollection?->removeAlias('foo');

		static::assertNotNull($storage->data);
		static::assertSame('foo', $storage->data->alias);
		static::assertNull($storage->data->serviceName);
	}

	public function testServiceAddedHook(): void
	{
		$storage = new ServiceAddedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$this->serviceCollection?->addSingleton('foo', 'bar');

		static::assertNotNull($storage->data);
		static::assertSame('foo', $storage->data->serviceDescriptor->serviceType);
		static::assertSame('bar', $storage->data->serviceDescriptor->implementationType);
	}

	public function testServiceRemovedHook(): void
	{
		$storage = new ServiceRemovedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$this->serviceCollection?->addSingleton('foo', 'bar');
		$this->serviceCollection?->remove('foo');

		static::assertNotNull($storage->data);
		static::assertSame('foo', $storage->data->serviceDescriptor->serviceType);
		static::assertSame('bar', $storage->data->serviceDescriptor->implementationType);
	}

	public function testServiceReplacedHook(): void
	{
		$storage = new ServiceReplacedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$this->serviceCollection?->addSingleton('foo', 'bar');
		$this->serviceCollection?->addSingleton('foo', 'baz', replace: true);
		$this->serviceCollection?->addSingleton('foo', 'buz');

		static::assertNotNull($storage->data);
		static::assertSame('foo', $storage->data->oldService->serviceType);
		static::assertSame('bar', $storage->data->oldService->implementationType);
		static::assertSame('foo', $storage->data->newService->serviceType);
		static::assertSame('baz', $storage->data->newService->implementationType);
	}

	public function testServiceRequestedHook(): void
	{
		$storage = new ServiceRequestedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$implementation = new stdClass();
		$this->serviceCollection?->addSingleton(stdClass::class, implementation: $implementation);
		$this->serviceCollection?->get(stdClass::class);

		static::assertNotNull($storage->data);
		static::assertSame(stdClass::class, $storage->data->serviceName);
		static::assertFalse($storage->data->hasServiceDescriptor());
		static::assertNull($storage->data->serviceDescriptor);
	}

	public function testServiceResolvedHook(): void
	{
		$storage = new ServiceResolvedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$implementation = new stdClass();
		$this->serviceCollection?->addSingleton(stdClass::class, implementation: $implementation);
		$this->serviceCollection?->get(stdClass::class);

		static::assertNotNull($storage->data);
		static::assertSame(stdClass::class, $storage->data->serviceName);
		static::assertSame($implementation, $storage->data->service);
		static::assertInstanceOf(ServiceDescriptor::class, $storage->data->serviceDescriptor);
	}

	public function testUnknownAliasRequestedHook(): void
	{
		$storage = new UnknownAliasRequestedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$this->serviceCollection?->getByAlias('foo');

		static::assertNotNull($storage->data);
		static::assertSame('foo', $storage->data->alias);
		static::assertNull($storage->data->serviceName);
	}

	public function testUnknownServiceRequestedHook(): void
	{
		$storage = new UnknownServiceRequestedCapturer();

		$this->serviceCollection?->registerHooks($storage);
		$this->serviceCollection?->getService(stdClass::class);

		static::assertNotNull($storage->data);
		static::assertSame(stdClass::class, $storage->data->serviceName);
		static::assertFalse($storage->data->hasServiceDescriptor());
	}
}

class AliasAddedCapturer implements AliasAddedHook
{
	public ?AliasHookData $data = null;

	public function aliasAdded(AliasHookData $data): void
	{
		$this->data = $data;
	}
}

class AliasRemovedCapturer implements AliasRemovedHook
{
	public ?AliasHookData $data = null;

	public function aliasRemoved(AliasHookData $data): void
	{
		$this->data = $data;
	}
}

class ServiceAddedCapturer implements ServiceAddedHook
{
	public ?ServiceDescriptorHookData $data = null;

	public function serviceAdded(ServiceDescriptorHookData $data): void
	{
		$this->data = $data;
	}
}

class ServiceRemovedCapturer implements ServiceRemovedHook
{
	public ?ServiceDescriptorHookData $data = null;

	public function serviceRemoved(ServiceDescriptorHookData $data): void
	{
		$this->data = $data;
	}
}

class ServiceReplacedCapturer implements ServiceReplacedHook
{
	public ?ServiceReplacedHookData $data = null;

	public function serviceReplaced(ServiceReplacedHookData $data): void
	{
		$this->data = $data;
	}
}

class ServiceRequestedCapturer implements ServiceRequestedHook
{
	public ?ServiceHookData $data = null;

	public function serviceRequested(ServiceHookData $data): void
	{
		$this->data = $data;
	}
}

class ServiceResolvedCapturer implements ServiceResolvedHook
{
	public ?ServiceResolvedHookData $data = null;

	public function serviceResolved(ServiceResolvedHookData $data): void
	{
		$this->data = $data;
	}
}

class UnknownAliasRequestedCapturer implements UnknownAliasRequestedHook
{
	public ?AliasHookData $data = null;

	public function unknownAliasRequested(AliasHookData $data): void
	{
		$this->data = $data;
	}
}

class UnknownServiceRequestedCapturer implements UnknownServiceRequestedHook
{
	public ?ServiceHookData $data = null;

	public function unknownServiceRequested(ServiceHookData $data): void
	{
		$this->data = $data;
	}
}
