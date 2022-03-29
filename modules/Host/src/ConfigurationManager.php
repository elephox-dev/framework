<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\Configuration\BuildsConfigurationRoot;
use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Contract\ConfigurationSource;
use Elephox\Configuration\Memory\MemoryConfigurationSource;
use Elephox\Configuration\ConfiguresConfigurationProviders;
use RuntimeException;

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
		return $this->configurationSources->select(fn(ConfigurationSource $source): ConfigurationProvider => $source->build($this));
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

	public function __serialize(): array
	{
		throw new RuntimeException('ConfigurationManager cannot be serialized');
	}

	public function __unserialize(array $data): void
	{
		throw new RuntimeException('ConfigurationManager cannot be unserialized');
	}
}
