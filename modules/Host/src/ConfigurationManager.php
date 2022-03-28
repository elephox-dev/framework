<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\Configuration\BuildsConfigurationRoot;
use Elephox\Configuration\Contract;
use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Contract\ConfigurationSource;
use Elephox\Configuration\ReadsConfigurationProviders;
use RuntimeException;

class ConfigurationManager implements ConfigurationBuilder, ConfigurationRoot
{
	use BuildsConfigurationRoot, ReadsConfigurationProviders;

	protected ObjectSet $configurationSources;

	public function __construct()
	{
		$this->configurationSources = new ObjectSet();
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
		return $this->configurationSources->select(fn(ConfigurationSource $source) => $source->build($this));
	}

	public function add(ConfigurationSource $source): static
	{
		$this->configurationSources->add($source);

		return $this;
	}

	protected function getBuilder(): Contract\ConfigurationBuilder
	{
		return $this;
	}

	protected function getRoot(): Contract\ConfigurationRoot
	{
		return $this;
	}

	public function __serialize(): array
	{
		return $this->build()->__serialize();
	}

	public function __unserialize(array $data): void
	{
		throw new RuntimeException('ConfigurationManager cannot be unserialized');
	}
}
