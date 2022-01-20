<?php

namespace Elephox\Core;

use Elephox\Core\Context\AbstractContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\RequestContext as RequestContextContract;
use Elephox\Core\Context\ExceptionContext;
use Elephox\Core\Context\Contract\ExceptionContext as ExceptionContextContract;
use Elephox\Core\Context\RequestContext;
use Elephox\Core\Contract\App;
use Elephox\Core\Handler\Contract\HandlerBinding;
use Elephox\Core\Handler\Contract\HandlerContainer as HandlerContainerContract;
use Elephox\DI\Contract\Container as ContainerContract;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\Response;
use Exception;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Core\Core
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\DI\Binding
 * @covers \Elephox\DI\Container
 * @covers \Elephox\DI\InstanceLifetime
 * @covers \Elephox\Core\Context\AbstractContext
 * @covers \Elephox\Core\Context\ExceptionContext
 * @covers \Elephox\Core\ActionType
 * @covers \Elephox\Core\Context\RequestContext
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Core\Handler\HandlerContainer
 * @uses \Elephox\Http\Contract\Request
 * @uses \Elephox\Http\Contract\Response
 * @uses \Elephox\Core\Registrar
 */
class CoreTest extends MockeryTestCase
{
	public function testCreateAndInstance(): void
	{
		$core = Core::instance();
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
			->with($appMock::class, $appMock)
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(App::class, $appMock::class)
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

	public function testRegisterAppObjectWithRegistrar(): void
	{
		$testApp = new TestAppWithRegistrar();

		$containerMock = M::mock(ContainerContract::class);
		$handlerContainerMock = M::mock(HandlerContainerContract::class);

		$containerMock
			->expects('register')
			->with(TestAppWithRegistrar::class, $testApp)
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(App::class, TestAppWithRegistrar::class)
			->andReturn()
		;

		$containerMock
			->expects('get')
			->with(TestAppWithRegistrar::class)
			->andReturn($testApp)
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
			->with(TestAppWithRegistrar::class)
			->andReturnSelf()
		;

		$core = new TestCore($containerMock);
		$instance = $core->registerApp($testApp);

		self::assertSame($testApp, $instance);
		self::assertTrue($testApp->registeredAll);
		self::assertSame($containerMock, $testApp->container);
	}

	public function testRegisterAppStringNoRegistrar(): void
	{
		$testApp = new TestAppNoRegistrar();

		$containerMock = M::mock(ContainerContract::class);
		$handlerContainerMock = M::mock(HandlerContainerContract::class);

		$containerMock
			->expects('register')
			->with(TestAppNoRegistrar::class, TestAppNoRegistrar::class)
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(App::class, TestAppNoRegistrar::class)
			->andReturn()
		;

		$containerMock
			->expects('get')
			->with(TestAppNoRegistrar::class)
			->andReturn($testApp)
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
			->with(TestAppNoRegistrar::class)
			->andReturnSelf()
		;

		$core = new TestCore($containerMock);
		$instance = $core->registerApp(TestAppNoRegistrar::class);

		self::assertSame($testApp, $instance);
		self::assertFalse($testApp->registeredAll);
		self::assertNull($testApp->container);
	}

	public function testHandleException(): void
	{
		$exceptionMock = M::mock(Exception::class);
		$containerMock = M::mock(ContainerContract::class);
		$handlerContainerMock = M::mock(HandlerContainerContract::class);
		$handlerBindingMock = M::mock(HandlerBinding::class);


		$containerMock
			->expects('register')
			->with(Context::class, M::capture($exceptionContext))
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(AbstractContext::class, Context::class)
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(ExceptionContext::class, Context::class)
			->andReturn()
		;

		$containerMock
			->expects('register')
			->with(ExceptionContextContract::class, M::capture($exceptionContext2))
			->andReturn()
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
			->expects('findHandler')
			->with(M::capture($exceptionContext3))
			->andReturn($handlerBindingMock)
		;

		$handlerBindingMock
			->expects('handle')
			->with(M::capture($exceptionContext4))
			->andReturn()
		;

		$core = new TestCore($containerMock);
		$core->handleException($exceptionMock);

		self::assertSame($exceptionContext, $exceptionContext2);
		self::assertSame($exceptionContext, $exceptionContext3);
		self::assertSame($exceptionContext, $exceptionContext4);
		self::assertSame($exceptionMock, $exceptionContext->getException());
	}

	public function testHandle(): void
	{
		$requestMock = M::mock(Request::class);
		$responseMock = M::mock(Response::class);
		$containerMock = M::mock(ContainerContract::class);
		$handlerContainerMock = M::mock(HandlerContainerContract::class);
		$handlerBindingMock = M::mock(HandlerBinding::class);

		$containerMock
			->expects('register')
			->with(Context::class, M::capture($requestContext))
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(AbstractContext::class, Context::class)
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(RequestContext::class, Context::class)
			->andReturn()
		;

		$containerMock
			->expects('register')
			->with(RequestContextContract::class, M::capture($requestContext2))
			->andReturn()
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
			->expects('findHandler')
			->with(M::capture($requestContext3))
			->andReturn($handlerBindingMock)
		;

		$handlerBindingMock
			->expects('handle')
			->with(M::capture($requestContext4))
			->andReturn($responseMock)
		;

		$textCore = new TestCore($containerMock);
		$response = $textCore->handle($requestMock);

		self::assertSame($requestContext, $requestContext2);
		self::assertSame($requestContext, $requestContext3);
		self::assertSame($requestContext, $requestContext4);
		self::assertSame($requestMock, $requestContext->getRequest());
		self::assertSame($responseMock, $response);
	}

	public function testHandleNoResponse(): void
	{
		$requestMock = M::mock(Request::class);
		$containerMock = M::mock(ContainerContract::class);
		$handlerContainerMock = M::mock(HandlerContainerContract::class);
		$handlerBindingMock = M::mock(HandlerBinding::class);

		$containerMock
			->expects('register')
			->with(Context::class, M::capture($requestContext))
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(AbstractContext::class, Context::class)
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(RequestContext::class, Context::class)
			->andReturn()
		;

		$containerMock
			->expects('register')
			->with(RequestContextContract::class, M::capture($requestContext2))
			->andReturn()
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
			->expects('findHandler')
			->with(M::capture($requestContext3))
			->andReturn($handlerBindingMock)
		;

		$handlerBindingMock
			->expects('handle')
			->with(M::capture($requestContext4))
			->andReturn()
		;

		$textCore = new TestCore($containerMock);

		$this->expectException(LogicException::class);
		$textCore->handle($requestMock);
	}

	public function testGetHandlerContainerAutoRegister(): void
	{
		$containerMock = M::mock(ContainerContract::class);
		$handlerContainerMock = M::mock(HandlerContainerContract::class);

		$containerMock
			->expects('has')
			->with(HandlerContainerContract::class)
			->andReturn(false)
		;

		$containerMock
			->expects('register')
			->with(HandlerContainerContract::class, M::capture($handlerContainer))
			->andReturn()
		;

		$containerMock
			->expects('get')
			->with(HandlerContainerContract::class)
			->andReturn($handlerContainerMock)
		;

		$core = new TestCore($containerMock);
		$handlerContainerReturned = $core->getHandlerContainer();

		self::assertSame($handlerContainerReturned, $handlerContainerMock);
		self::assertInstanceOf(HandlerContainerContract::class, $handlerContainer);
	}
}

class TestCore extends Core
{
	public function __construct(ContainerContract $container)
	{
		parent::__construct($container);
	}
}

class TestAppNoRegistrar implements App
{
	public bool $registeredAll = false;
	public ?ContainerContract $container = null;

	public function registerAll(ContainerContract $container): void
	{
		$this->registeredAll = true;
		$this->container = $container;
	}
}

class TestAppWithRegistrar implements App
{
	use Registrar;

	public bool $registeredAll = false;
	public ?ContainerContract $container = null;

	public function registerAll(ContainerContract $container): void
	{
		$this->registeredAll = true;
		$this->container = $container;
	}
}
