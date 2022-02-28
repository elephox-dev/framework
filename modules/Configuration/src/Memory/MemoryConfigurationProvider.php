<?php
declare(strict_types=1);

namespace Elephox\Configuration\Memory;

use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\HasArrayData;

class MemoryConfigurationProvider implements ConfigurationProvider
{
	use HasArrayData;

	public function __construct(
		MemoryConfigurationSource $source,
	) {
		$this->data = [];
		/** @psalm-suppress MixedAssignment */
		foreach ($source->data as $key => $value) {
			$this->data[$key] = $value;
		}
	}
}
