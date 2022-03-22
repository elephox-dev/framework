<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Web\Contract\WebHostEnvironment;
use Elephox\Web\Contract\WebServiceCollection;

class WebApplicationBuilder
{
	public function __construct(
		public readonly ConfigurationBuilder&ConfigurationRoot $configuration,
		public readonly WebHostEnvironment $environment,
		public readonly WebServiceCollection $services,
		public readonly RequestPipelineBuilder $pipeline,
	)
	{
		$configuration->add(new JsonFileConfigurationSource($environment->getRootDirectory()->getFile("config.json")->getPath()));
		$configuration->add(new JsonFileConfigurationSource($environment->getRootDirectory()->getFile("config.{$environment->getEnvironmentName()}.json")->getPath(), true));
		$configuration->add(new JsonFileConfigurationSource($environment->getRootDirectory()->getFile("config.local.json")->getPath(), true));

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
}
