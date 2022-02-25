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
	/**
	 * @param ObjectSet<Contract\ConfigurationProvider> $providers
	 */
	#[Pure]
	public function __construct(
		private readonly ObjectSet $providers
	) {
	}

	/**
	 * @return GenericEnumerable<string>
	 */
	public function getChildKeys(string|Str|null $path = null): GenericEnumerable
	{
		return $this->providers
			->selectMany(function (mixed $provider) use ($path): GenericEnumerable {
				/** @var Contract\ConfigurationProvider $provider */
				return $provider->getChildKeys($path)
					->select(function (string $key) use ($path): string {
						return $path === null ? $key : ConfigurationPath::appendKey($path, $key)->getSource();
					});
			})
			->distinct()
			->where(fn(string $key): bool => $path === null || Str::wrap($key)->startsWith($path))
		;
	}

	public function getChildren(string|Str|null $path = null): GenericEnumerable
	{
		return $this->getChildKeys($path)->select(fn(string $key) => $this->getSection($key));
	}

	public function hasSection(string|Str $key): bool
	{
		/** @var Contract\ConfigurationProvider $provider */
		foreach ($this->providers->reverse() as $provider) {
			if ($provider->tryGet($key, $value)) {
				return true;
			}
		}

		return false;
	}

	public function getSection(string|Str $key): Contract\ConfigurationSection
	{
		return new ConfigurationSection($this, $key);
	}

	public function getProviders(): GenericEnumerable
	{
		return $this->providers;
	}

	#[ArrayShape(['providers' => "array"])]
	public function __serialize(): array
	{
		return [
			'providers' => $this->providers->toArray()
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
			$this->providers->add($provider);
		}
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Offset must be a string');
		}

		return $this->hasSection($offset);
	}

	public function offsetGet(mixed $offset): string|int|float|bool|null
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException("Offset must be a string");
		}

		/** @var Contract\ConfigurationProvider $provider */
		foreach ($this->providers->reverse() as $provider) {
			if ($provider->tryGet($offset, $value)) {
				return $value;
			}
		}

		return null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException("Offset must be a string");
		}

		if (!is_scalar($value) && !is_null($value)) {
			throw new InvalidArgumentException("Value must be a scalar or null");
		}

		if ($this->providers->isEmpty()) {
			throw new RuntimeException("No providers available");
		}

		/** @var Contract\ConfigurationProvider $provider */
		foreach ($this->providers as $provider) {
			$provider->set($offset, $value);
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException("Offset must be a string");
		}

		/** @var Contract\ConfigurationProvider $provider */
		foreach ($this->providers as $provider) {
			$provider->remove($offset);
		}
	}
}
