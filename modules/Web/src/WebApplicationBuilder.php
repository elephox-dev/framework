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
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebEnvironment;
use Elephox\Web\Middleware\DefaultExceptionHandler;
use Elephox\Web\Middleware\ServerTimingHeaderMiddleware;
use Elephox\Web\Routing\RequestRouter;

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
		$pipeline ??= new RequestPipelineBuilder(new class implements RequestPipelineEndpoint {
			public function handle(RequestContract $request): ResponseBuilder
			{
				return Response::build()->responseCode(ResponseCode::BadRequest);
			}
		});

		$services->addSingleton(Environment::class, implementation: $environment);
		$services->addSingleton(WebEnvironment::class, implementation: $environment);

		$services->addSingleton(Configuration::class, implementation: $configuration);

		$services->addSingleton(ExceptionHandler::class, DefaultExceptionHandler::class);

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
		// Load .env, .env.local
		$this->loadDotEnvFile();

		// Load config.json, config.local.json
		$this->loadConfigFile();

		// Load .env.{$ENVIRONMENT}, .env.{$ENVIRONMENT}.local
		$this->loadEnvironmentDotEnvFile();

		// Load config.{$ENVIRONMENT}.json, config.{$ENVIRONMENT}.local.json
		$this->loadEnvironmentConfigFile();

		$this->addDefaultMiddleware();
	}

	protected function getEnvironment(): Environment
	{
		return $this->environment;
	}

	protected function getConfigurationBuilder(): ConfigurationBuilderContract
	{
		return $this->configuration;
	}

	protected function addDefaultMiddleware(): void
	{
		$this->pipeline->push(new ServerTimingHeaderMiddleware('pipeline'));
	}

	public function build(): WebApplication
	{
		$configuration = $this->configuration->build();
		$this->services->addSingleton(Configuration::class, implementation: $configuration, replace: true);

		$builtPipeline = $this->pipeline->build();
		$this->services->addSingleton(RequestPipeline::class, implementation: $builtPipeline);

		return new WebApplication(
			$this->services,
			$configuration,
			$this->environment,
			$builtPipeline,
		);
	}

	public function setRequestRouterEndpoint(?RequestRouter $router = null): RequestRouter
	{
		$router ??= new RequestRouter($this->services);
		$this->services->addSingleton(RequestRouter::class, implementation: $router, replace: true);
		$this->pipeline->endpoint($router);

		return $router;
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
}
