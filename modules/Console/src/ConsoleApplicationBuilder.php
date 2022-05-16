<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\Configuration;
use Elephox\Configuration\Contract\ConfigurationBuilder as ConfigurationBuilderContract;
use Elephox\Configuration\Contract\ConfigurationManager as ConfigurationManagerContract;
use Elephox\Configuration\Contract\Environment;
use Elephox\Configuration\LoadsDefaultConfiguration;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;
use Elephox\Logging\AnsiColorSink;
use Elephox\Logging\ConsoleSink;
use Elephox\Logging\MultiSinkLogger;
use Elephox\Support\Contract\ErrorHandler;
use Elephox\Support\Contract\ExceptionHandler;
use Psr\Log\LoggerInterface;

/**
 * @psalm-consistent-constructor
 */
class ConsoleApplicationBuilder
{
	use LoadsDefaultConfiguration;

	public static function create(
		?ServiceCollectionContract $services = null,
		?ConfigurationManager $configuration = null,
		?ConsoleEnvironment $environment = null,
		?CommandCollection $commands = null,
	): static {
		$configuration ??= new ConfigurationManager();
		$environment ??= new GlobalConsoleEnvironment();
		$services ??= new ServiceCollection();
		$commands ??= new CommandCollection($services->resolver());

		$services->addSingleton(Environment::class, implementation: $environment);
		$services->addSingleton(ConsoleEnvironment::class, implementation: $environment);

		$services->addSingleton(Configuration::class, implementation: $configuration);

		$services->addSingleton(CommandCollection::class, implementation: $commands);

		$services->addSingleton(ExceptionHandler::class, DefaultExceptionHandler::class);
		$services->addSingleton(ErrorHandler::class, DefaultExceptionHandler::class);

		return new static(
			$configuration,
			$environment,
			$services,
			$commands,
		);
	}

	public function __construct(
		public readonly ConfigurationManagerContract $configuration,
		public readonly ConsoleEnvironment $environment,
		public readonly ServiceCollectionContract $services,
		public readonly CommandCollection $commands,
	) {
		// Load .env, .env.local
		$this->loadDotEnvFile();

		// Load config.json, config.local.json
		$this->loadConfigFile();

		// Load .env.{$ENVIRONMENT}, .env.{$ENVIRONMENT}.local
		$this->loadEnvironmentDotEnvFile();

		// Load config.{$ENVIRONMENT}.json, config.{$ENVIRONMENT}.local.json
		$this->loadEnvironmentConfigFile();
	}

	protected function getEnvironment(): Environment
	{
		return $this->environment;
	}

	protected function getServices(): ServiceCollectionContract
	{
		return $this->services;
	}

	protected function getCommands(): CommandCollection
	{
		return $this->commands;
	}

	protected function getConfigurationBuilder(): ConfigurationBuilderContract
	{
		return $this->configuration;
	}

	public function build(): ConsoleApplication
	{
		$configuration = $this->configuration->build();
		$this->services->addSingleton(Configuration::class, implementation: $configuration, replace: true);

		if ($this->services->has(ErrorHandler::class)) {
			set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
				return $this->services->requireService(ErrorHandler::class)->handleError($severity, $message, $file, $line);
			});
		}

		return new ConsoleApplication(
			$this->services,
			$configuration,
			$this->environment,
			$this->commands,
		);
	}

	/**
	 * @template T of object
	 *
	 * @param class-string<T>|string $name
	 *
	 * @return T
	 */
	public function service(string $name): object
	{
		/** @var T */
		return $this->services->require($name);
	}

	public function addLogging(): void
	{
		$this->services->addSingleton(LoggerInterface::class, MultiSinkLogger::class, static function (): MultiSinkLogger {
			$logger = new MultiSinkLogger();
			$logger->addSink(new AnsiColorSink(new ConsoleSink()));

			return $logger;
		});
	}
}
