<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use JetBrains\PhpStorm\Pure;

class ConfigurationRoot implements Contract\ConfigurationRoot
{
	use ConfiguresConfigurationProviders {
		offsetGet as protected innerOffsetGet;
	}
	use SubstitutesEnvironmentVariables;

	/**
	 * @param ObjectSet<Contract\ConfigurationProvider> $providers
	 */
	#[Pure]
	public function __construct(
		private readonly ObjectSet $providers,
	) {
	}

	public function getProviders(): GenericEnumerable
	{
		return $this->providers;
	}

	protected function getRoot(): Contract\ConfigurationRoot
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
