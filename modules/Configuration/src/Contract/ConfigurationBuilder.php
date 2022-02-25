<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use Elephox\Collection\Contract\GenericEnumerable;

interface ConfigurationBuilder
{
	/**
	 * @return GenericEnumerable<ConfigurationSource>
	 */
	public function getSources(): GenericEnumerable;

	public function add(ConfigurationSource $source): static;

	public function build(): ConfigurationRoot;
}
