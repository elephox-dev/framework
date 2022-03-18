<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\OOR\Str;
use InvalidArgumentException;
use RuntimeException;

trait ReadsConfigurationProviders
{
	/**
	 * @return GenericEnumerable<Contract\ConfigurationProvider>
	 */
	abstract protected function getProviders(): GenericEnumerable;

	abstract protected function getRoot(): Contract\ConfigurationRoot;

	/**
	 * @return GenericEnumerable<string>
	 */
	public function getChildKeys(string|Str|null $path = null): GenericEnumerable
	{
		return $this->getProviders()
			->selectMany(function (mixed $provider) use ($path): GenericEnumerable {
				/** @var Contract\ConfigurationProvider $provider */
				return $provider->getChildKeys($path)
					->select(function (string $key) use ($path): string {
						return $path === null ? $key : ConfigurationPath::appendKey($path, $key)->getSource();
					});
			})
			->distinct()
			->where(fn(string $key): bool => $path === null || Str::wrap($key)->startsWith($path));
	}

	public function getChildren(string|Str|null $path = null): GenericEnumerable
	{
		return $this->getChildKeys($path)->select(fn(string $key) => $this->getSection($key));
	}

	public function hasSection(string|Str $key): bool
	{
		/** @var Contract\ConfigurationProvider $provider */
		foreach ($this->getProviders()->reverse() as $provider) {
			if ($provider->tryGet($key, $value)) {
				return true;
			}
		}

		return false;
	}

	public function getSection(string|Str $key): Contract\ConfigurationSection
	{
		return new ConfigurationSection($this->getRoot(), $key);
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
		foreach ($this->getProviders()->reverse() as $provider) {
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

		$providers = $this->getProviders()->toList();
		if (empty($providers)) {
			throw new RuntimeException("No providers available");
		}

		/** @var Contract\ConfigurationProvider $provider */
		foreach ($providers as $provider) {
			$provider->set($offset, $value);
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException("Offset must be a string");
		}

		/** @var Contract\ConfigurationProvider $provider */
		foreach ($this->getProviders() as $provider) {
			$provider->remove($offset);
		}
	}
}
