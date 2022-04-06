<?php

namespace Elephox\Console;

use Elephox\Logging\Contract\Logger;
use Elephox\Support\Contract\ExceptionHandler;
use Throwable;

class DefaultExceptionHandler implements ExceptionHandler
{
	public function __construct(
		private readonly Logger $logger,
	)
	{
	}

	public function handleException(Throwable $exception): void
	{
		$this->logger->critical("Uncaught exception!");
		$this->logger->critical($exception->getMessage());
		$this->logger->critical($exception->getTraceAsString());
	}
}
