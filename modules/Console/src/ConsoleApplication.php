<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Command\CommandNotFoundException;
use Elephox\Console\Command\HelpCommand;
use Elephox\Console\Command\NoCommandInCommandLineException;
use Elephox\Console\Command\RawCommandInvocation;
use Elephox\Console\Command\RequiredArgumentMissingException;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\Logging\Contract\Logger;
use Elephox\Support\Contract\ExceptionHandler;

class ConsoleApplication
{
	protected ?Logger $logger = null;
	protected ?ExceptionHandler $exceptionHandler = null;

	public function __construct(
		public readonly ServiceCollectionContract $services,
		public readonly Configuration $configuration,
		public readonly ConsoleEnvironment $environment,
		public readonly CommandCollection $commands,
	) {
		$this->services->addSingleton(__CLASS__, implementation: $this);
	}

	public function logger(): Logger
	{
		if ($this->logger === null) {
			$this->logger = $this->services->requireService(Logger::class);
		}

		return $this->logger;
	}

	public function exceptionHandler(): ExceptionHandler
	{
		if ($this->exceptionHandler === null) {
			$this->exceptionHandler = $this->services->requireService(ExceptionHandler::class);
		}

		return $this->exceptionHandler;
	}

	public function run(): void
	{
		global $argv;

		$this->commands->loadFromClass(HelpCommand::class);

		try {
			$invocation = RawCommandInvocation::fromCommandLine($argv);

			$code = $this->handle($invocation);
		} catch (CommandNotFoundException|NoCommandInCommandLineException $e) {
			$this->logger()->error($e->getMessage());

			$this->handle(RawCommandInvocation::fromCommandLine([$argv[0], 'help']));
			$code = 1;
		} catch (RequiredArgumentMissingException $e) {
			$this->logger()->error($e->getMessage());

			$this->logger()->error("Use '" . implode(' ', [$argv[0], 'help', $argv[1]]) . "' to get help for this command.");
			$code = 1;
		}

		exit($code);
	}

	public function handle(RawCommandInvocation $invocation): int
	{
		$compiled = $this->commands->findCompiled($invocation);

		return $compiled->handler->handle($invocation->build($compiled->template)) ?? 0;
	}
}
