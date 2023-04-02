<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Logging\EnhancedMessageSink;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\Contract\SinkProxy;
use Elephox\Logging\LogLevel;
use Elephox\Logging\SimpleFormatColorSink;
use Elephox\Logging\SingleSinkLogger;
use Elephox\Logging\StandardSink;
use Psr\Log\LoggerInterface;

readonly class LoggerCommand implements CommandHandler
{
	public function __construct(
		private LoggerInterface $logger,
		private ServiceCollection $services,
	) {
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder->setName('logger');
		$builder->setDescription('Choose a logger to test');
		$builder->addOption('sink', default: EnhancedMessageSink::class, description: 'The sink to use');
	}

	public function handle(CommandInvocation $command): int|null
	{
		/** @var string $sinkClass */
		$sinkClass = $command->options->get('sink')->value;
		if (!class_exists($sinkClass)) {
			$this->logger->error("Class '$sinkClass' does not exist");

			return 1;
		}

		$interfaces = class_implements($sinkClass);
		if (!$interfaces || !in_array(Sink::class, $interfaces, true)) {
			$this->logger->error("Class '$sinkClass' does not implement Sink");

			return 1;
		}

		$args = [];
		if (in_array(SinkProxy::class, $interfaces, true)) {
			$args['innerSink'] = new SimpleFormatColorSink(new StandardSink());
		}

		/** @var Sink $sink */
		$sink = $this->services->resolver()->instantiate($sinkClass, $args);

		$logger = new SingleSinkLogger($sink);
		foreach (LogLevel::cases() as $level) {
			$logger->log($level, strtolower($level->name) . ' message');
		}

		return 0;
	}
}
