<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\ObjectSet;
use Elephox\Configuration\Contract\ConfigurationSource;

trait BuildsConfigurationRoot
{
	/**
	 * @return iterable<ConfigurationSource>
	 */
	abstract protected function getSources(): iterable;

	public function build(): Contract\ConfigurationRoot
	{
		/** @var ObjectSet<Contract\ConfigurationProvider> $providers */
		$providers = new ObjectSet();

		foreach ($this->getSources() as $source) {
			$providers->add($source->build());
		}

		return new ConfigurationRoot($providers);
	}
}
