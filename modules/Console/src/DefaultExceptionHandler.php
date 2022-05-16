<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Support\Contract\ErrorHandler;
use Elephox\Support\Contract\ExceptionHandler;
use Psr\Log\LoggerInterface;
use Throwable;

class DefaultExceptionHandler implements ExceptionHandler, ErrorHandler
{
	public function __construct(
		private readonly LoggerInterface $logger,
	) {
	}

	public function handleException(Throwable $exception): void
	{
		$this->logger->critical('Uncaught exception!');
		$this->logger->critical($exception->getMessage(), ['file' => $exception->getFile(), 'line' => $exception->getLine()]);
		$this->logger->critical($exception->getTraceAsString());
	}

	public function handleError(int $severity, string $message, string $file, int $line): bool
	{
		if (!(error_reporting() & $severity)) {
			return false;
		}

		switch ($severity) {
			case E_USER_DEPRECATED:
			case E_DEPRECATED:
			case E_USER_WARNING:
			case E_WARNING:
				$this->logger->warning($message, ['file' => $file, 'line' => $line]);
				break;
			case E_USER_NOTICE:
			case E_NOTICE:
				$this->logger->notice($message, ['file' => $file, 'line' => $line]);
				break;
			case E_USER_ERROR:
			case E_ERROR:
				$this->logger->error($message, ['file' => $file, 'line' => $line]);
				break;
			default:
				$this->logger->critical($message, ['file' => $file, 'line' => $line]);
				exit(1);
		}

		return true;
	}
}
