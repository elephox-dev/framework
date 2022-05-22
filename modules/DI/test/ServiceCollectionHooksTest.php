<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Hooks\AliasHookData;
use Elephox\DI\Hooks\Contract\AliasAddedHook;
use PHPUnit\Framework\TestCase;

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
}

class AliasAddedCapturer implements AliasAddedHook
{
	public ?AliasHookData $data = null;

	public function aliasAdded(AliasHookData $data): void
	{
		$this->data = $data;
	}
}
