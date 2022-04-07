<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\OOR\Str;
use InvalidArgumentException;

class ConfigurationSection implements Contract\ConfigurationSection
{
	private ?Str $key = null;

	public function __construct(
		private readonly Contract\ConfigurationRoot $root,
		private readonly string|Str $path,
	) {
	}

	/**
	 * @return GenericEnumerable<string>
	 */
	public function getChildKeys(string|Str|null $path = null): GenericEnumerable
	{
		return $this->root->getChildKeys($path !== null ? ConfigurationPath::appendKey($this->path, (string) $path) : $this->path);
	}

	public function getChildren(string|Str|null $path = null): GenericEnumerable
	{
		return $this->root->getChildren($path !== null ? ConfigurationPath::appendKey($this->path, (string) $path) : $this->path);
	}

	public function hasSection(string|Str $key): bool
	{
		return $this->getChildKeys()->contains((string) $key);
	}

	public function getSection(string|Str $key): Contract\ConfigurationSection
	{
		return new self($this->root, ConfigurationPath::appendKey($this->path, (string) $key));
	}

	public function getKey(): string
	{
		if ($this->key === null) {
			$this->key = ConfigurationPath::getSectionKey($this->path);
		}

		return (string) $this->key;
	}

	public function getValue(): string|int|float|bool|null
	{
		return $this->root->offsetGet($this->getPath());
	}

	public function setValue(float|bool|int|string|null $value): void
	{
		$this->root->offsetSet($this->getPath(), $value);
	}

	public function deleteValue(): void
	{
		$this->root->offsetUnset($this->getPath());
	}

	public function getPath(): string
	{
		return (string) $this->path;
	}

	public function offsetGet(mixed $offset): string|int|float|bool|null
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Offset must be a string');
		}

		return $this->root->offsetGet((string) ConfigurationPath::appendKey($this->path, $offset));
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Offset must be a string');
		}

		return $this->root->offsetExists((string) ConfigurationPath::appendKey($this->path, $offset));
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Offset must be a string');
		}

		$this->root->offsetSet((string) ConfigurationPath::appendKey($this->path, $offset), $value);
	}

	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Offset must be a string');
		}

		$this->root->offsetUnset((string) ConfigurationPath::appendKey($this->path, $offset));
	}
}
