<?php
declare(strict_types=1);

namespace Elephox\Configuration\Json;

use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\HasArrayData;
use Elephox\Configuration\Json\Contract\JsonDataConfigurationSource;
use JsonException;

class JsonConfigurationProvider implements ConfigurationProvider
{
	use HasArrayData;

	/**
	 * @throws JsonException
	 *
	 * @param privateJsonDataConfigurationSource $source
	 */
	public function __construct(
		private JsonDataConfigurationSource $source,
	) {
		$this->data = [];
		foreach ($this->source->getData() as $key => $value) {
			$this->data[$key] = $value;
		}
	}
}
