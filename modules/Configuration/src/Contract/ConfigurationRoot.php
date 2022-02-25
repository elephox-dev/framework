<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use Elephox\Collection\Contract\GenericEnumerable;

interface ConfigurationRoot extends Configuration
{
	/**
	 * @return GenericEnumerable<ConfigurationProvider>
	 */
	public function getProviders(): GenericEnumerable;

	public function __serialize(): array;

	public function __unserialize(array $data): void;
}
