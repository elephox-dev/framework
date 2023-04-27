<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Console\Command\CommandNotFoundException;
use Elephox\Console\Command\CommandProvider;
use Elephox\Console\Command\EmptyCommandLineException;
use Elephox\Console\Command\IncompleteCommandLineException;
use Elephox\Console\Command\NoCommandInCommandLineException;
use Elephox\Console\Command\RawCommandInvocation;
use Elephox\Console\Command\RequiredArgumentMissingException;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\RootServiceProvider;
use Elephox\Support\Contract\ExceptionHandler;
use JsonException;
use Psr\Log\LoggerInterface;

class ConsoleApplication
{
	protected ?LoggerInterface $logger = null;
	protected ?CommandProvider $commands = null;
	protected ?ExceptionHandler $exceptionHandler = null;

	public function __construct(
		public readonly RootServiceProvider $services,
		public readonly Configuration $configuration,
		public readonly ConsoleEnvironment $environment,
	) {
	}

	public function logger(): LoggerInterface
	{
		if ($this->logger === null) {
			$this->logger = $this->services->require(LoggerInterface::class);
		}

		return $this->logger;
	}

	public function commands(): CommandProvider
	{
		if ($this->commands === null) {
			$this->commands = $this->services->require(CommandProvider::class);
		}

		return $this->commands;
	}

	public function exceptionHandler(): ExceptionHandler
	{
		if ($this->exceptionHandler === null) {
			$this->exceptionHandler = $this->services->require(ExceptionHandler::class);
		}

		return $this->exceptionHandler;
	}

	public function run(): never
	{
		global $argv;

		try {
			$invocation = RawCommandInvocation::fromCommandLine($argv);

			try {
				if ($this->needsHelp($invocation)) {
					$code = $this->showHelpFor($invocation);
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
		} catch (EmptyCommandLineException|NoCommandInCommandLineException $e) {
			$this->logger()->error($e->getMessage());
			$this->logger()->error("Use the 'help' command to get a list of available commands.");
		} catch (IncompleteCommandLineException $e) {
			$this->logger()->error($e->getMessage());
			$this->logger()->error('Please check the syntax of your invoked command line. For help, refer to https://elephox.dev/console/syntax');
		} catch (JsonException $e) {
			$this->logger()->error($e->getMessage());
		} finally {
			$code ??= 1;
		}

		exit($code);
	}

	protected function needsHelp(RawCommandInvocation $invocation): bool
	{
		return $invocation->parameters->has('help') || $invocation->parameters->has('?');
	}

	/**
	 * @throws JsonException
	 */
	protected function showHelpFor(RawCommandInvocation $invocation): int
	{
		return $this->handle(RawCommandInvocation::fromCommandLine([$invocation->invokedBinary, 'help', $invocation->name]));
	}

	public function handle(RawCommandInvocation $invocation): int
	{
		$handler = $this->commands()->get($invocation->name);

		return $handler->handle($invocation);
	}
}
