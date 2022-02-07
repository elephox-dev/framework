<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\CommandLineContext;
use Elephox\Core\Handler\Attribute\CommandHandler;
use Elephox\Logging\Contract\Logger;

#[CommandHandler(weight: -100)]
class DefaultCommandHandler
{
	public function __invoke(CommandLineContext $context, ?Logger $logger): int
	{
		if ($logger === null) {
			$logger = new class {
				public function error(string $message, array $metaData = []): void
				{
					fwrite(STDERR, $message . PHP_EOL);
					if (!empty($metaData)) {
						/** @var string $json */
						$json = json_encode($metaData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
						fwrite(STDERR, $json . PHP_EOL);
					}
				}
			};
		}

		if (empty($context->getCommand())) {
			$logger->error('No command specified.');
		} else {
			$logger->error("Command not found: {$context->getCommand()}");
		}

		return 1;
	}
}
