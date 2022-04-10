<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\Configuration;
use Elephox\Configuration\Contract\ConfigurationManager as ConfigurationManagerContract;
use Elephox\Configuration\Contract\Environment;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;
use Elephox\Logging\ConsoleSink;
use Elephox\Logging\Contract\Logger;
use Elephox\Logging\MessageFormatterSink;
use Elephox\Logging\MultiSinkLogger;
use Elephox\Support\Contract\ExceptionHandler;
use Whoops\Run as WhoopsRun;
use Whoops\RunInterface as WhoopsRunInterface;

/**
 * @psalm-consistent-constructor
 */
class ConsoleApplicationBuilder
{
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

	protected function loadDotEnvFile(): void
	{
		$this->environment->loadFromEnvFile();
	}

	protected function loadEnvironmentDotEnvFile(): void
	{
		$this->environment->loadFromEnvFile($this->environment->getEnvironmentName());
	}

	protected function loadConfigFile(): void
	{
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile('config.json')
				->getPath(),
			true,
		));

		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile('config.local.json')
				->getPath(),
			true,
		));
	}

	protected function loadEnvironmentConfigFile(): void
	{
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.{$this->environment->getEnvironmentName()}.json")
				->getPath(),
			true,
		));

		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.{$this->environment->getEnvironmentName()}.local.json")
				->getPath(),
			true,
		));
	}

	public function build(): ConsoleApplication
	{
		$configuration = $this->configuration->build();
		$this->services->addSingleton(Configuration::class, implementation: $configuration, replace: true);

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

	public function addLogging(): self
	{
		$this->services->addSingleton(Logger::class, MultiSinkLogger::class, static function (): MultiSinkLogger {
			$logger = new MultiSinkLogger();
			$logger->addSink(new MessageFormatterSink(new ConsoleSink()));

			return $logger;
		});

		return $this;
	}

	public function addWhoops(): self
	{
		$this->services->addSingleton(WhoopsRunInterface::class, WhoopsRun::class);
		$this->services->addSingleton(ExceptionHandler::class, WhoopsExceptionHandler::class, replace: true);

		return $this;
	}
}
