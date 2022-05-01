<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Contract\ConfigurationSource;
use Elephox\Configuration\Memory\MemoryConfigurationSource;

class ConfigurationManager implements Contract\ConfigurationManager
{
	use BuildsConfigurationRoot;
	use ConfiguresConfigurationProviders {
		offsetGet as protected innerOffsetGet;
	}
	use SubstitutesEnvironmentVariables;

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
		return $this->configurationSources->select(static fn (object $source): ConfigurationProvider => /** @var ConfigurationSource $source */ $source->build());
	}

	public function add(ConfigurationSource $source): static
	{
		$this->configurationSources->add($source);

		return $this;
	}

	protected function getRoot(): ConfigurationRoot
	{
		return $this;
	}

	/**
	 * @param mixed $offset
	 *
	 * @return array|string|int|float|bool|null
	 */
	public function offsetGet(mixed $offset): array|string|int|float|bool|null
	{
		$value = $this->innerOffsetGet($offset);

		if (is_string($value)) {
			return $this->substituteEnvironmentVariables($value);
		}

		if (is_iterable($value)) {
			return [...$this->substituteEnvironmentVariablesRecursive($value)];
		}

		return $value;
	}
}
