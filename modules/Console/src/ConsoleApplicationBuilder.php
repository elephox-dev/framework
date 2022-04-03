<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Logging\ConsoleSink;
use Elephox\Logging\Contract\Logger;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\GenericSinkLogger;
use Whoops\Handler\PlainTextHandler;
use Whoops\RunInterface as WhoopsRunInterface;
use Whoops\Run as WhoopsRun;

class ConsoleApplicationBuilder
{
	public function __construct(
		public readonly ConfigurationBuilder&ConfigurationRoot $configuration,
		public readonly ConsoleEnvironment $environment,
		public readonly ServiceCollection $services,
		public readonly CommandCollection $commands,
	)
	{
		$this->registerDefaultConfig();
		$this->setDebugFromConfig();
	}

	public function registerDefaultConfig(): void
	{
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.json")
				->getPath()
		));
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.{$this->environment->getEnvironmentName()}.json")
				->getPath(),
			true
		));
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.local.json")
				->getPath(),
			true
		));
	}

	public function setDebugFromConfig(): void
	{
		if ($this->configuration->hasSection("env:debug")) {
			$this->environment->offsetSet('APP_DEBUG', (bool)$this->configuration['env:debug']);
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
	 * @return T
	 */
	public function service(string $name): object
	{
		/** @var T */
		return $this->services->require($name);
	}

	public function addLogging(): void
	{
		$this->services->addSingleton(Logger::class, GenericSinkLogger::class, function (Sink $sink): GenericSinkLogger {
			return new GenericSinkLogger($sink);
		});

		$this->services->addSingleton(Sink::class, implementation: new ConsoleSink());
	}

	public function addWhoops(): void
	{
		$this->services->addSingleton(WhoopsRunInterface::class, WhoopsRun::class, implementationFactory: function () {
			$whoops = new WhoopsRun();
			$whoops->pushHandler(new PlainTextHandler());
			return $whoops;
		});
	}
}
