<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\Configuration;
use Elephox\Configuration\Contract\ConfigurationBuilder as ConfigurationBuilderContract;
use Elephox\Configuration\Contract\ConfigurationManager as ConfigurationManagerContract;
use Elephox\Configuration\Contract\Environment;
use Elephox\Configuration\LoadsDefaultConfiguration;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;
use Elephox\Support\Contract\ErrorHandler;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\WebEnvironment;
use Elephox\Web\Middleware\DefaultExceptionHandler;
use Elephox\Web\Middleware\DefaultNotFoundHandler;
use Elephox\Web\Middleware\FileExtensionToContentType;
use Elephox\Web\Middleware\StaticContentHandler;
use Elephox\Web\Routing\Contract\Router;
use Elephox\Web\Routing\Contract\RouterBuilder as RouterBuilderContract;
use Elephox\Web\Routing\RouterBuilder;
use Elephox\Web\Routing\RouterEndpoint;
use Throwable;

/**
 * @psalm-consistent-constructor
 */
class WebApplicationBuilder
{
	use LoadsDefaultConfiguration;

	public static function create(
		?ServiceCollectionContract $services = null,
		?ConfigurationManagerContract $configuration = null,
		?WebEnvironment $environment = null,
		?RequestPipelineBuilder $pipeline = null,
	): static {
		$configuration ??= new ConfigurationManager();
		$environment ??= new GlobalWebEnvironment();
		$services ??= new ServiceCollection();

		$pipeline ??= new RequestPipelineBuilder(null, null);

		$services->addSingleton(Environment::class, instance: $environment);
		$services->addSingleton(WebEnvironment::class, instance: $environment);
		$services->addSingleton(Configuration::class, instance: $configuration);

		return new static(
			$configuration,
			$environment,
			$services,
			$pipeline,
		);
	}

	public function __construct(
		public readonly ConfigurationManagerContract $configuration,
		public readonly WebEnvironment $environment,
		public readonly ServiceCollectionContract $services,
		public readonly RequestPipelineBuilder $pipeline,
	) {
		$this->loadConfiguration();

		$this->addGlobalMiddleware();

		$this->addDefaultExceptionHandler();
	}

	protected function getEnvironment(): WebEnvironment
	{
		return $this->environment;
	}

	protected function getServices(): ServiceCollectionContract
	{
		return $this->services;
	}

	protected function getPipeline(): RequestPipelineBuilder
	{
		return $this->pipeline;
	}

	protected function getConfigurationBuilder(): ConfigurationBuilderContract
	{
		return $this->configuration;
	}

	public function getRouter(): RouterBuilderContract
	{
		return $this->service(RouterBuilderContract::class);
	}

	protected function loadConfiguration(): void
	{
		// Load .env, .env.local
		$this->loadDotEnvFile();

		// Load config.json, config.local.json
		$this->loadConfigFile();

		// Load .env.{$ENVIRONMENT}, .env.{$ENVIRONMENT}.local
		$this->loadEnvironmentDotEnvFile();

		// Load config.{$ENVIRONMENT}.json, config.{$ENVIRONMENT}.local.json
		$this->loadEnvironmentConfigFile();
	}

	protected function addGlobalMiddleware(): void
	{
		$this->pipeline->push(new DefaultNotFoundHandler());
		$this->pipeline->push(new FileExtensionToContentType());
		$this->pipeline->push(new StaticContentHandler($this->getEnvironment()->getWebRoot()));
	}

	public function addDefaultExceptionHandler(): void
	{
		$handler = new DefaultExceptionHandler();

		$this->getServices()->addSingleton(ExceptionHandler::class, instance: $handler);
		$this->pipeline->exceptionHandler($handler);
	}

	public function build(): WebApplication
	{
		$configuration = $this->configuration->build();
		$this->services->addSingleton(Configuration::class, instance: $configuration);

		$provider = $this->services->buildProvider();

		$builtPipeline = $this->pipeline->build($provider);
		$this->services->addSingleton(RequestPipeline::class, instance: $builtPipeline);

		if ($provider->has(ExceptionHandler::class)) {
			set_exception_handler(static function (Throwable $exception) use ($provider): void {
				$provider->get(ExceptionHandler::class)
					->handleException($exception)
				;
			});
		}

		if ($provider->has(ErrorHandler::class)) {
			set_error_handler(
				static function (int $severity, string $message, string $file, int $line) use ($provider): bool {
					return $provider->get(ErrorHandler::class)
						->handleError($severity, $message, $file, $line)
					;
				},
			);
		}

		return new WebApplication(
			$provider,
			$configuration,
			$this->environment,
			$builtPipeline,
		);
	}

	public function addRouting(): void
	{
		$this->services->addSingleton(RouterBuilderContract::class, RouterBuilder::class);
		$this->services->addSingleton(Router::class, factory: static fn (RouterBuilderContract $routerBuilder): Router => $routerBuilder->build());
		$this->pipeline->endpoint(RouterEndpoint::class);
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
		return $this->services->get($name);
	}
}
