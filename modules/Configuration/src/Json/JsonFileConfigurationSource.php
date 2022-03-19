<?php
declare(strict_types=1);

namespace Elephox\Configuration\Json;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\Json\Contract\JsonDataConfigurationSource;
use InvalidArgumentException;
use JsonException;

class JsonFileConfigurationSource implements JsonDataConfigurationSource
{
	public function __construct(
		public readonly string $path,
		public readonly bool $optional = false,
	)
	{
	}

	/**
	 * @return array<string, string|null>
	 *
	 * @throws JsonException
	 */
	public function getData(): array
	{
		if (!file_exists($this->path)) {
			if ($this->optional) {
				return [];
			}

			throw new InvalidArgumentException(
				sprintf('File "%s" does not exist and is not optional', $this->path)
			);
		}

		$json = file_get_contents($this->path);
		if (!$json)
		{
			throw new JsonException("File '$this->path' could not be read");
		}

		/** @var array<string, string|null> */
		return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
	}

	/**
	 * @throws JsonException
	 */
	public function build(ConfigurationBuilder $builder): ConfigurationProvider
	{
		return new JsonConfigurationProvider($this);
	}
}
