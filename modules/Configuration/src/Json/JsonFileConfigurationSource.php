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
		private readonly string $path,
		private readonly bool $optional = false,
	)
	{
		if (!$optional && !file_exists($this->path)) {
			throw new InvalidArgumentException(
				sprintf('File "%s" does not exist and is not optional', $this->path)
			);
		}
	}

	/**
	 * @return array<string, string|null>
	 *
	 * @throws JsonException
	 */
	public function getData(): array
	{
		if ($this->optional && !file_exists($this->path)) {
			return [];
		}

		$json = file_get_contents($this->path);
		if ($json === false)
		{
			throw new JsonException(
				sprintf('File "%s" could not be read', $this->path)
			);
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
