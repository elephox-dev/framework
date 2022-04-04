<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Command\CommandNotFoundException;
use Elephox\Console\Command\HelpCommand;
use Elephox\Console\Command\NoCommandInCommandLineException;
use Elephox\Console\Command\RawCommandInvocation;
use Elephox\Console\Command\RequiredArgumentMissingException;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;
use Elephox\Logging\Contract\Logger;

class ConsoleApplication
{
	public function __construct(
		public readonly ServiceCollectionContract $services,
		public readonly ConsoleEnvironment $environment,
		public readonly ConfigurationRoot $configuration,
	)
	{
	}

	public static function createBuilder(): ConsoleApplicationBuilder
	{
		$configuration = new ConfigurationManager();
		$environment = new GlobalConsoleEnvironment();
		$services = new ServiceCollection();
		$commands = new CommandCollection($services->resolver());

		return new ConsoleApplicationBuilder(
			$configuration,
			$environment,
			$services,
			$commands,
		);
	}

	public function run(): never
	{
		global $argv;

		$this->services->requireService(CommandCollection::class)->loadFromClass(HelpCommand::class);

		try {
			$invocation = RawCommandInvocation::fromCommandLine($argv);

			$code = $this->handle($invocation);
		} catch (CommandNotFoundException|NoCommandInCommandLineException $e) {
			$this->services->requireService(Logger::class)->error($e->getMessage());

			$this->handle(RawCommandInvocation::fromCommandLine([$argv[0], 'help']));
			$code = 1;
		} catch (RequiredArgumentMissingException $e) {
			$this->services->requireService(Logger::class)->error($e->getMessage());

			$this->handle(RawCommandInvocation::fromCommandLine([$argv[0], 'help', $argv[1]]));
			$code = 1;
		}

		exit($code);
	}

	public function handle(RawCommandInvocation $invocation): int
	{
		$compiled = $this->services
			->requireService(CommandCollection::class)
			->findCompiled($invocation);

		return $compiled->handler->handle($invocation->build($compiled->template)) ?? 0;
	}
}
