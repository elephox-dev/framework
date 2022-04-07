<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use JetBrains\PhpStorm\Pure;

class ConfigurationRoot implements Contract\ConfigurationRoot
{
	use ConfiguresConfigurationProviders;

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
}
