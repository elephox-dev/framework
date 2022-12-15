<?php
declare(strict_types=1);

namespace Elephox\Configuration\Json;

use Elephox\Configuration\Contract\ConfigurationProvider;
use Elephox\Configuration\Json\Contract\JsonDataConfigurationSource;
use Elephox\Files\Contract\File;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use JsonException;

class JsonFileConfigurationSource implements JsonDataConfigurationSource
{
	public function __construct(
		public readonly File $jsonFile,
		public readonly bool $optional = false,
	) {
	}

	/**
	 * @return array<string, string|null>
	 *
	 * @throws JsonException
	 */
	public function getData(): array
	{
		if (!$this->jsonFile->exists()) {
			if ($this->optional) {
				return [];
			}

			throw new InvalidArgumentException(
				sprintf('File "%s" does not exist and is not optional', $this->jsonFile->path()),
			);
		}

		$json = $this->jsonFile->contents();
		if (!$json) {
			throw new JsonException("File '{$this->jsonFile->path()}' could not be read");
		}

		/** @var array<string, string|null> */
		return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
	}

	/**
	 * @throws JsonException
	 */
	#[Pure]
	public function build(): ConfigurationProvider
	{
		return new JsonConfigurationProvider($this);
	}
}
