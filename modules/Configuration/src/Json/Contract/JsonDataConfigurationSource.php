<?php
declare(strict_types=1);

namespace Elephox\Configuration\Json\Contract;

use Elephox\Configuration\Contract\ConfigurationSource;
use JsonException;

interface JsonDataConfigurationSource extends ConfigurationSource
{
	/**
	 * @return array<string, string|null>
	 *
	 * @throws JsonException
	 */
	public function getData(): array;
}
