<?php

namespace Elephox\Core;

use Elephox\Core\Contract\App;
use Elephox\Core\Handler\Contract\HandlerContainer as HandlerContainerContract;
use Elephox\DI\Contract\Container as ContainerContract;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Core\Core
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\DI\Binding
 * @covers \Elephox\DI\Container
 * @covers \Elephox\DI\InstanceLifetime
 */
class CoreTest extends MockeryTestCase
{
	public function testCreateAndInstance(): void
	{
		$core = Core::create();
		self::assertInstanceOf(Core::class, $core);

		$instance = Core::instance();
		self::assertSame($core, $instance);

		self::assertTrue($instance->getContainer()->has($instance::class));
		self::assertSame($instance, $instance->getContainer()->get($instance::class));
		self::assertTrue($instance->getContainer()->has(Contract\Core::class));
		self::assertSame($instance, $instance->getContainer()->get(Contract\Core::class));
		self::assertTrue(defined('ELEPHOX_VERSION'));

		$instance2 = Core::instance();
		self::assertSame($core, $instance2);

		$this->expectException(LogicException::class);
		Core::create();
	}

	public function testConstructor(): void
	{
		$containerMock = M::mock(ContainerContract::class);

		$core = new TestCore($containerMock);

		self::assertSame($containerMock, $core->getContainer());
	}

	public function testVersion(): void
	{
		$core = new TestCore(M::mock(ContainerContract::class));

		self::assertMatchesRegularExpression("/\d+\.\d+(?:\d+)?/", $core->getVersion());
	}

	public function testRegisterAppObjectNoRegistrar(): void
	{
		$appMock = M::mock(App::class);
		$containerMock = M::mock(ContainerContract::class);
		$handlerContainerMock = M::mock(HandlerContainerContract::class);

		$containerMock
			->expects('register')
			->with(App::class, $appMock)
			->andReturn()
		;

		$containerMock
			->expects('register')
			->with($appMock::class, $appMock)
			->andReturn()
		;

		$containerMock
			->expects('get')
			->with($appMock::class)
			->andReturn($appMock)
		;

		$containerMock
			->expects('has')
			->with(HandlerContainerContract::class)
			->andReturn(true)
		;

		$containerMock
			->expects('get')
			->with(HandlerContainerContract::class)
			->andReturn($handlerContainerMock)
		;

		$handlerContainerMock
			->expects('loadFromClass')
			->with($appMock::class)
			->andReturnSelf()
		;

		$core = new TestCore($containerMock);
		$instance = $core->registerApp($appMock);

		self::assertSame($appMock, $instance);
	}
}

class TestCore extends Core
{
	public function __construct(ContainerContract $container)
	{
		parent::__construct($container);
	}
}
