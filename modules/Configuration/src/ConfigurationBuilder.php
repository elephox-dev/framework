<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\Configuration\Contract\ConfigurationSource;
use JetBrains\PhpStorm\Pure;

class ConfigurationBuilder implements Contract\ConfigurationBuilder
{
	private ObjectSet $sources;

	#[Pure]
	public function __construct()
	{
		$this->sources = new ObjectSet();
	}

	public function getSources(): GenericEnumerable
	{
		return $this->sources;
	}

	public function add(ConfigurationSource $source): static
	{
		$this->sources->add($source);

		return $this;
	}

	public function build(): Contract\ConfigurationRoot
	{
		$providers = new ObjectSet();

		foreach ($this->sources as $source)
		{
			$providers->add($source->build($this));
		}

		return new ConfigurationRoot($providers);
	}
}
