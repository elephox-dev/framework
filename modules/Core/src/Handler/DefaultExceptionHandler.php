<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\ExceptionContext;
use Elephox\Core\Handler\Attribute\ExceptionHandler;
use Elephox\Logging\Contract\Logger;

#[ExceptionHandler(weight: -100)]
class DefaultExceptionHandler
{
	public function __invoke(ExceptionContext $context, ?Logger $logger = null): void
	{
		if ($logger === null) {
			$logger = new class {
				public function error(string $message, array $metaData = []): void
				{
					fwrite(STDERR, $message . PHP_EOL);
					if (array_key_exists('exception', $metaData)) {
						fwrite(STDERR, $metaData['exception']->getTraceAsString() . PHP_EOL);
					} else if (!empty($metaData)) {
						fwrite(STDERR, json_encode($metaData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL);
					}
				}
			};
		}

		$logger->error("An unhandled exception occurred: {$context->getException()->getMessage()}", [
			'exception' => $context->getException()
		]);
	}
}
