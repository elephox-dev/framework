<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\Configuration;
use Elephox\Configuration\Contract\ConfigurationBuilder as ConfigurationBuilderContract;
use Elephox\Configuration\Contract\ConfigurationManager as ConfigurationManagerContract;
use Elephox\Configuration\Contract\Environment;
use Elephox\Configuration\LoadsDefaultConfiguration;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;
use Elephox\Http\Contract\RequestMethod;
use Elephox\Support\Contract\ErrorHandler;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\WebEnvironment;
use Elephox\Web\Middleware\DefaultExceptionHandler;
use Elephox\Web\Middleware\DefaultNotFoundHandler;
use Elephox\Web\Middleware\FileExtensionToContentType;
use Elephox\Web\Middleware\StaticContentHandler;
use Elephox\Web\Routing\ClassRouteLoader;
use Elephox\Web\Routing\ClosureRouteLoader;
use Elephox\Web\Routing\Contract\Router;
use Elephox\Web\Routing\NamespaceRouteLoader;
use Elephox\Web\Routing\RegexRouter;
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

		$pipeline ??= new RequestPipelineBuilder(null, null, $services->resolver());

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
		$this->services->addSingleton(Configuration::class, instance: $configuration, replace: true);

		$builtPipeline = $this->pipeline->build();
		$this->services->addSingleton(RequestPipeline::class, instance: $builtPipeline, replace: true);

		if ($this->services->has(ExceptionHandler::class)) {
			set_exception_handler(function (Throwable $exception): void {
				$this->services->requireService(ExceptionHandler::class)
					->handleException($exception)
				;
			});
		}

		if ($this->services->has(ErrorHandler::class)) {
			set_error_handler(
				function (int $severity, string $message, string $file, int $line): bool {
					return $this->services->requireService(ErrorHandler::class)
						->handleError($severity, $message, $file, $line)
					;
				},
			);
		}

		return new WebApplication(
			$this->services,
			$configuration,
			$this->environment,
			$builtPipeline,
		);
	}

	public function addRouting(): void
	{
		$this->services->addSingleton(Router::class, RegexRouter::class);
		$this->pipeline->endpoint(RouterEndpoint::class);
	}

	/**
	 * @var class-string $className
	 */
	public function addRoutesFromClass(string $className): void
	{
		$loader = $this->resolver()->instantiate(ClassRouteLoader::class, ['className' => $className]);
		$router = $this->service(Router::class);
		$router->addLoader($loader);
	}

	public function addRoutesFromNamespace(string $namespace): void
	{
		$loader = $this->resolver()->instantiate(NamespaceRouteLoader::class, ['namespace' => $namespace]);
		$router = $this->service(Router::class);
		$router->addLoader($loader);
	}

	public function addRoute(RequestMethod|string|iterable $method, string $template, callable $handler): void
	{
		$loader = new ClosureRouteLoader($method, $template, $handler(...));
		$router = $this->service(Router::class);
		$router->addLoader($loader);
	}

	/**
	 * @template T of object
	 *
	 * @param class-string<T>|string $name
	 *
	 * @psalm-suppress InvalidReturnType psalm is unable to verify T as the return type
	 *
	 * @return T
	 */
	public function service(string $name): object
	{
		/** @var T */
		return $this->services->require($name);
	}

	public function resolver(): Resolver
	{
		return $this->services->resolver();
	}
}
