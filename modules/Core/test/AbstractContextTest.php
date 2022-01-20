<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Context\AbstractContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\DI\Contract\Container;
use Mockery as M;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Elephox\Core\Context\AbstractContext
 * @covers \Elephox\Core\ActionType
 * @covers \Elephox\DI\InstanceLifetime
 */
class AbstractContextTest extends MockeryTestCase
{
	public function testGetters(): void
	{
		$containerMock = M::mock(Container::class);
		$containerMock
			->expects('register')
			->with(Context::class, M::capture($context1))
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(AbstractContext::class, Context::class)
			->andReturn()
		;

		$containerMock
			->expects('alias')
			->with(TestContext::class, Context::class)
			->andReturn()
		;

		$context = new TestContext(ActionType::Any, $containerMock);

		self::assertSame(ActionType::Any, $context->getActionType());
		self::assertSame($containerMock, $context->getContainer());
	}
}

class TestContext extends AbstractContext
{
	public function __construct(ActionType $actionType, Container $container)
	{
		parent::__construct($actionType, $container);
	}
}
