<?php
/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */
declare(strict_types=1);

namespace Elephox\Web;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\ServiceCollection;
use Elephox\Web\Contract\WebHostEnvironment;
use LogicException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;
use Whoops\RunInterface as WhoopsRunInterface;

class WebServiceCollection extends ServiceCollection implements Contract\WebServiceCollection
{
	public function addDoctrine(?callable $configurator = null): Contract\WebServiceCollection
	{
		if (!class_exists('Doctrine\ORM\EntityManager') || !class_exists('Doctrine\ORM\Tools\Setup')) {
			throw new LogicException('Unable to find Doctrine ORM. Did you forget to add doctrine/orm to your composer.json?');
		}

		/**
		 * @psalm-suppress UndefinedClass
		 * @psalm-suppress MixedAssignment
		 * @psalm-suppress UnusedVariable
		 * @psalm-suppress UnusedClosureParam
		 * @psalm-suppress MixedInferredReturnType
		 * @psalm-suppress MixedReturnStatement
		 */
		$configurator ??= function (): void {
			$this->addSingleton(
				EntityManagerInterface::class,
				EntityManager::class,
				implementationFactory: /** @psalm-suppress UndefinedClass */ static function (ConfigurationRoot $configuration, ?WebHostEnvironment $environment = null): EntityManagerInterface {
					$setupDriver = $configuration['doctrine:metadata:driver'];
					$setupMethod = match ($setupDriver) {
						'annotation' => 'createAnnotationMetadataConfiguration',
						'yaml' => 'createYAMLMetadataConfiguration',
						'xml' => 'createXMLMetadataConfiguration',
						default => throw new ConfigurationException('Unsupported doctrine metadata driver: ' . $setupDriver),
					};

					$setupConfig = DoctrineSetup::{$setupMethod}(
						$configuration['doctrine:metadata:paths'],
						$configuration['doctrine:dev'] ?? $environment?->isDevelopment() ?? false,
					);

					return EntityManager::create($configuration['doctrine:connection'], $setupConfig);
				}
			 );
		};

		$this->resolver->callback($configurator(...));

		return $this;
	}

	/**
	 * @psalm-suppress UndefinedClass
	 * @psalm-suppress MixedArgument
	 */
	public function addWhoops(?callable $configurator = null): Contract\WebServiceCollection
	{
		if (!class_exists('Whoops\Run')) {
			throw new LogicException('Unable to find Whoops. Did you forget to add filp/whoops to your composer.json?');
		}

		$this->addSingleton(
			WhoopsRunInterface::class,
			implementation: new WhoopsRun(),
		);

		$configurator ??= /** @psalm-suppress UndefinedClass */ static function (WhoopsRun $whoops): void {
			$whoops->pushHandler(new PrettyPageHandler);
			$whoops->register();
		};

		$this->resolver->callback($configurator(...));

		return $this;
	}
}
