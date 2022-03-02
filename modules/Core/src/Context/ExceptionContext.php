<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\ActionType;
use Elephox\Core\Context\Contract\Context;
use Elephox\DI\Contract\Container;
use Throwable;

class ExceptionContext extends AbstractContext implements Contract\ExceptionContext
{
	public function __construct(
		Container $container,
		private readonly Context $original,
		private readonly Throwable $exception,
	)
	{
		parent::__construct(ActionType::Exception, $container);

		$container->register(Contract\ExceptionContext::class, $this);
	}

	public function getOriginal(): Context
	{
		return $this->original;
	}

	public function getException(): Throwable
	{
		return $this->exception;
	}
}
