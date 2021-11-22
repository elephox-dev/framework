<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\Handler\ActionType;
use Elephox\DI\Contract\Container;
use JetBrains\PhpStorm\Pure;
use Throwable;

class ExceptionContext extends AbstractContext implements Contract\ExceptionContext
{
	#[Pure] public function __construct(
		Container $container,
		private Throwable $exception
	)
	{
		parent::__construct(ActionType::Exception, $container);

		$container->register(Contract\ExceptionContext::class, $this);
	}

	public function getException(): Throwable
	{
		return $this->exception;
	}
}
