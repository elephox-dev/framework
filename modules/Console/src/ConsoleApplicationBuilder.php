<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\ConfigurationManager;
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
		?ConfigurationManager $configuration = null,
		?ConsoleEnvironment $environment = null,
		?ServiceCollectionContract $services = null,
		?CommandCollection $commands = null,
	): static {
		$configuration ??= new ConfigurationManager();
		$environment ??= new GlobalConsoleEnvironment();
		$services ??= new ServiceCollection();
		$commands ??= new CommandCollection($services->resolver());

		$services->addSingleton(Environment::class, implementation: $environment);
		$services->addSingleton(ConsoleEnvironment::class, implementation: $environment);
		$services->addSingleton(CommandCollection::class, implementation: $commands);

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
		$this->registerDefaultExceptionHandler();
		$this->registerDefaultConfig();
		$this->setEnvNameFromConfig();
		$this->registerOptionalConfig();
		$this->setDebugModeFromConfig();
	}

	protected function registerDefaultExceptionHandler(): void
	{
		$this->services->addSingleton(ExceptionHandler::class, DefaultExceptionHandler::class);
	}

	protected function registerDefaultConfig(): void
	{
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile('config.json')
				->getPath(),
		));
	}

	protected function registerOptionalConfig(): void
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
				->getFile('config.local.json')
				->getPath(),
			true,
		));
	}

	public function setEnvNameFromConfig(): void
	{
		if ($this->configuration->hasSection('env:name')) {
			$this->environment->offsetSet('APP_ENV', (string) $this->configuration['env:name']);
		}
	}

	public function setDebugModeFromConfig(): void
	{
		if ($this->configuration->hasSection('env:debug')) {
			$this->environment->offsetSet('APP_DEBUG', (bool) $this->configuration['env:debug']);
		}
	}

	public function build(): ConsoleApplication
	{
		$this->services->addSingleton(CommandCollection::class, implementation: $this->commands);

		return new ConsoleApplication(
			$this->services,
			$this->environment,
			$this->configuration->build(),
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
		$this->services->removeService(ExceptionHandler::class);
		$this->services->addSingleton(WhoopsRunInterface::class, WhoopsRun::class);
		$this->services->addSingleton(ExceptionHandler::class, WhoopsExceptionHandler::class);

		return $this;
	}
}
