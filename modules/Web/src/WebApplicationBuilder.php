<?php
declare(strict_types=1);

namespace Elephox\Web;

use Doctrine\ORM\Configuration as DoctrineConfiguration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationManager as ConfigurationManagerContract;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebEnvironment;
use Elephox\Web\Middleware\DefaultExceptionHandler;
use Elephox\Web\Middleware\ProcessingTimeHeader;
use Elephox\Web\Middleware\WhoopsExceptionHandler;
use Elephox\Web\Routing\RequestRouter;
use Whoops\Run as WhoopsRun;
use Whoops\RunInterface as WhoopsRunInterface;

/**
 * @psalm-consistent-constructor
 */
class WebApplicationBuilder
{
	public static function create(
		?ConfigurationManagerContract $configuration = null,
		?WebEnvironment $environment = null,
		?ServiceCollectionContract $services = null,
		?RequestPipelineBuilder $pipeline = null,
	): static {
		$configuration ??= new ConfigurationManager();
		$environment ??= new GlobalWebEnvironment();
		$services ??= new \Elephox\DI\ServiceCollection();
		$pipeline ??= new RequestPipelineBuilder(new class implements RequestPipelineEndpoint {
			public function handle(RequestContract $request): ResponseBuilder
			{
				return Response::build()->responseCode(ResponseCode::BadRequest);
			}
		});

		$pipeline->push(new ProcessingTimeHeader());

		return new static(
			$configuration,
			$environment,
			$services,
			$pipeline,
		);
	}

	public function __construct(
		public readonly ConfigurationBuilder & ConfigurationRoot $configuration,
		public readonly WebEnvironment $environment,
		public readonly ServiceCollection $services,
		public readonly RequestPipelineBuilder $pipeline,
	) {
		$this->registerDefaultExceptionHandler();
		$this->registerDefaultConfig();
		$this->setDebugFromConfig();
	}

	protected function registerDefaultExceptionHandler(): void
	{
		$this->services->addSingleton(ExceptionHandler::class, DefaultExceptionHandler::class);
	}

	public function registerDefaultConfig(): void
	{
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile('config.json')
				->getPath(),
		));
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

	public function setDebugFromConfig(): void
	{
		if ($this->configuration->hasSection('env:debug')) {
			$this->environment->offsetSet('APP_DEBUG', (bool) $this->configuration['env:debug']);
		}
	}

	public function build(): WebApplication
	{
		$builtPipeline = $this->pipeline->build();
		$this->services->addSingleton(RequestPipeline::class, implementation: $builtPipeline);

		return new WebApplication(
			$this->environment,
			$this->services,
			$this->configuration->build(),
		);
	}

	/**
	 * @param null|callable(WhoopsRunInterface): void $configurator
	 */
	public function addWhoops(?callable $configurator = null): void
	{
		$this->services->removeService(ExceptionHandler::class);

		$this->services->addSingleton(WhoopsRunInterface::class, WhoopsRun::class);

		if ($configurator) {
			$configurator($this->services->requireService(WhoopsRunInterface::class));
		}

		$whoopsExceptionHandler = new WhoopsExceptionHandler(fn () => $this->services->requireService(WhoopsRunInterface::class));

		$this->pipeline->push($whoopsExceptionHandler);
		$this->services->addSingleton(ExceptionHandler::class, implementation: $whoopsExceptionHandler);
	}

	/**
	 * @param null|callable(mixed): \Doctrine\ORM\Configuration $setup
	 */
	public function addDoctrine(?callable $setup = null): void
	{
		$this->services->addSingleton(
			EntityManagerInterface::class,
			EntityManager::class,
			implementationFactory: function (ConfigurationRoot $configuration) use ($setup): EntityManagerInterface {
				$setup ??= static function (ConfigurationRoot $conf, WebEnvironment $env): DoctrineConfiguration {
					$setupDriver = $conf['doctrine:metadata:driver'];
					$setupMethod = match ($setupDriver) {
						'annotation' => 'createAnnotationMetadataConfiguration',
						'yaml' => 'createYAMLMetadataConfiguration',
						'xml' => 'createXMLMetadataConfiguration',
						null => throw new ConfigurationException('No doctrine metadata driver specified at "doctrine:metadata:driver"'),
						default => throw new ConfigurationException('Unsupported doctrine metadata driver: ' . $setupDriver),
					};

					/** @var DoctrineConfiguration */
					return DoctrineSetup::{$setupMethod}(
						$conf['doctrine:metadata:paths'],
						$conf['doctrine:dev'] ?? $env->isDevelopment(),
					);
				};

				/**
				 * @psalm-suppress ArgumentTypeCoercion
				 */
				$setupConfig = $this->services->resolver()->callback($setup);
				$connection = $configuration['doctrine:connection'];
				if ($connection === null) {
					throw new ConfigurationException('No doctrine connection specified at "doctrine:connection"');
				}

				/**
				 * @psalm-suppress InvalidArgument
				 */
				return EntityManager::create($connection, $setupConfig);
			},
		);
	}

	public function setRequestRouterEndpoint(): void
	{
		$router = new RequestRouter($this->services);
		$this->services->addSingleton(RequestRouter::class, implementation: $router);
		$this->pipeline->endpoint($router);
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
