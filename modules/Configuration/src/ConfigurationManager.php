<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Contract\ConfigurationSource;
use Elephox\Configuration\Memory\MemoryConfigurationSource;

class ConfigurationManager implements Contract\ConfigurationManager
{
	use BuildsConfigurationRoot, ConfiguresConfigurationProviders;

	protected ObjectSet $configurationSources;

	public function __construct()
	{
		$this->configurationSources = new ObjectSet();
		$this->configurationSources->add(new MemoryConfigurationSource([]));
	}

	/**
	 * @return GenericEnumerable<ConfigurationSource>
	 */
	public function getSources(): GenericEnumerable
	{
		return $this->configurationSources;
	}

	/**
	 * @return GenericEnumerable<ConfigurationProvider>
	 */
	public function getProviders(): GenericEnumerable
	{
		return $this->configurationSources->select(static fn(object $source): ConfigurationProvider => /** @var ConfigurationSource $source */ $source->build());
	}

	public function add(ConfigurationSource $source): static
	{
		$this->configurationSources->add($source);

		return $this;
	}

	protected function getBuilder(): ConfigurationBuilder
	{
		return $this;
	}

	protected function getRoot(): ConfigurationRoot
	{
		return $this;
	}
}
