<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Support\Contract\ExceptionHandler;
use Psr\Log\LoggerInterface;
use Throwable;

class DefaultExceptionHandler implements ExceptionHandler
{
	public function __construct(
		private readonly LoggerInterface $logger,
	) {
	}

	public function handleException(Throwable $exception): void
	{
		$this->logger->critical('Uncaught exception!');
		$this->logger->critical($exception->getMessage());
		$this->logger->critical($exception->getTraceAsString());
	}
}
