<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Command\CommandNotFoundException;
use Elephox\Console\Command\EmptyCommandLineException;
use Elephox\Console\Command\HelpCommand;
use Elephox\Console\Command\IncompleteCommandLineException;
use Elephox\Console\Command\NoCommandInCommandLineException;
use Elephox\Console\Command\RawCommandInvocation;
use Elephox\Console\Command\RequiredArgumentMissingException;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\Support\Contract\ExceptionHandler;
use Psr\Log\LoggerInterface;

class ConsoleApplication
{
	protected ?LoggerInterface $logger = null;
	protected ?ExceptionHandler $exceptionHandler = null;

	public function __construct(
		public readonly ServiceCollectionContract $services,
		public readonly Configuration $configuration,
		public readonly ConsoleEnvironment $environment,
		public readonly CommandCollection $commands,
	) {
		$this->services->addSingleton(__CLASS__, implementation: $this);
	}

	public function logger(): LoggerInterface
	{
		if ($this->logger === null) {
			$this->logger = $this->services->requireService(LoggerInterface::class);
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

	public function run(): never
	{
		global $argv;

		$this->commands->loadFromClass(HelpCommand::class);

		try {
			$invocation = RawCommandInvocation::fromCommandLine($argv);

			try {
				if ($invocation->arguments->has('help') || $invocation->arguments->has('?')) {
					$code = $this->handle(RawCommandInvocation::fromCommandLine([$invocation->invokedBinary, 'help', $invocation->name]));
				} else {
					$code = $this->handle($invocation);
				}
			} catch (CommandNotFoundException $e) {
				$this->logger()->error($e->getMessage());
				$this->logger()->error("Try '$invocation->invokedBinary help' to get a list of available commands.");
			} catch (RequiredArgumentMissingException $e) {
				$this->logger()->error($e->getMessage());
				$this->logger()->error("Use '$invocation->invokedBinary $invocation->name --help' to get help for this command.");
			}
		} catch (EmptyCommandLineException $e) {
		} catch (NoCommandInCommandLineException $e) {
		} catch (IncompleteCommandLineException $e) {
		} finally {
			$code ??= 1;
		}

		exit($code);
	}

	public function handle(RawCommandInvocation $invocation): int
	{
		$compiled = $this->commands->findCompiled($invocation);

		return $compiled->handler->handle($invocation->build($compiled->template)) ?? 0;
	}
}
