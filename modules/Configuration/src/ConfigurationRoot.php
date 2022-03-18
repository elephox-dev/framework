<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\OOR\Str;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class ConfigurationRoot implements Contract\ConfigurationRoot
{
	use ReadsConfigurationProviders;

	/**
	 * @param ObjectSet<Contract\ConfigurationProvider> $providers
	 */
	#[Pure]
	public function __construct(
		private readonly ObjectSet $providers
	) {
	}

	public function getProviders(): GenericEnumerable
	{
		return $this->providers;
	}

	protected function getRoot(): Contract\ConfigurationRoot
	{
		return $this;
	}

	#[ArrayShape(['providers' => "array"])]
	public function __serialize(): array
	{
		return [
			'providers' => $this->getProviders()->toArray()
		];
	}

	public function __unserialize(array $data): void
	{
		if (!array_key_exists('providers', $data)) {
			throw new InvalidArgumentException('Missing "providers" key in data');
		}

		/** @var ObjectSet<Contract\ConfigurationProvider> */
		$this->providers = new ObjectSet();

		/** @var Contract\ConfigurationProvider $provider */
		foreach ($data['providers'] as $provider) {
			$this->getProviders()->add($provider);
		}
	}
}
