<?php
declare(strict_types=1);

namespace Elephox\Web;

use Doctrine\ORM\Configuration as DoctrineConfiguration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\DI\Contract\Resolver;
use Elephox\Web\Contract\WebHostEnvironment;
use Elephox\Web\Contract\WebServiceCollection;
use Elephox\Web\Middleware\WhoopsExceptionHandler;
use Elephox\Web\Routing\RequestRouter;
use Whoops\Run as WhoopsRun;
use Whoops\RunInterface as WhoopsRunInterface;

class WebApplicationBuilder
{
	public function __construct(
		public readonly ConfigurationBuilder&ConfigurationRoot $configuration,
		public readonly WebHostEnvironment $environment,
		public readonly WebServiceCollection $services,
		public readonly RequestPipelineBuilder $pipeline,
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
	 *
	 * @return void
	 */
	public function addWhoops(?callable $configurator = null): void
	{

		$this->services->addSingleton(
			WhoopsRunInterface::class,
			implementation: new WhoopsRun(),
		);

		if ($configurator) {
			$configurator($this->services->requireService(WhoopsRunInterface::class));
		}

		$this->pipeline->push(new WhoopsExceptionHandler(fn () => $this->services->requireService(WhoopsRunInterface::class)));
	}

	/**
	 * @param null|callable(mixed): \Doctrine\ORM\Configuration $setup
	 * @return void
	 */
	public function addDoctrine(?callable $setup = null): void
	{
		$this->services->addSingleton(
			EntityManagerInterface::class,
			EntityManager::class,
			implementationFactory: function (ConfigurationRoot $configuration) use ($setup): EntityManagerInterface {
				$setup ??= static function (ConfigurationRoot $conf, WebHostEnvironment $env): DoctrineConfiguration {
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
			}
		);
	}

	public function setRequestRouterEndpoint(): void
	{
		$router = new RequestRouter($this->services);
		$this->services->addSingleton(RequestRouter::class, implementation: $router);
		$this->pipeline->endpoint($router);
	}
}
