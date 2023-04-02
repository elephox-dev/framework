<?php
declare(strict_types=1);

namespace Elephox\Configuration\Memory;

use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\Contract\ConfigurationSource;
use JetBrains\PhpStorm\Pure;

readonly class MemoryConfigurationSource implements ConfigurationSource
{
	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(
		public array $data,
	) {
	}

	#[Pure]
	public function build(): ConfigurationProvider
	{
		return new MemoryConfigurationProvider($this);
	}
}
