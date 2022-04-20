<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Dotenv\Dotenv;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\KeyedEnumerable;
use Elephox\Files\Directory;
use Elephox\Support\TransparentGetterSetter;
use InvalidArgumentException;
use RuntimeException;

class MemoryEnvironment extends AbstractEnvironment implements Contract\Environment
{
	use TransparentGetterSetter;

	protected ?Directory $cachedRootDirectory = null;

	/**
	 * @var array<string, scalar|null>
	 */
	protected array $memory = [];

	public function __construct(public readonly ?string $rootPath = null)
	{
	}

	public function loadFromEnvFile(?string $envName = null): void
	{
		$envFile = '.env';
		if ($envName !== null) {
			$envFile .= '.' . $envName;
		}

		$dotenv = Dotenv::createImmutable($this->getRoot()->getPath(), $envFile);
		$dotenvLocal = Dotenv::createImmutable($this->getRoot()->getPath(), $envFile . '.local');

		$this->memory = array_merge($this->memory, $dotenv->safeLoad(), $dotenvLocal->safeLoad());
	}

	public function getRoot(): Directory
	{
		if ($this->cachedRootDirectory !== null) {
			return $this->cachedRootDirectory;
		}

		if ($this->rootPath !== null) {
			$dir = new Directory($this->rootPath);
		} elseif ($this->offsetExists('APP_ROOT')) {
			$dir = new Directory((string) $this['APP_ROOT']);
		} else {
			$cwd = getcwd();
			if (!$cwd) {
				throw new RuntimeException('Cannot get current working directory');
			}

			$dir = new Directory($cwd);
		}

		$this->cachedRootDirectory = $dir;

		return $dir;
	}

	public function offsetExists(mixed $offset): bool
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		return isset($this->memory[$offset]);
	}

	/**
	 * @param mixed $offset
	 *
	 * @return scalar|null
	 */
	public function offsetGet(mixed $offset): string|int|bool|null|float
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		/** @var mixed $value */
		$value = $this->memory[$offset] ?? null;

		if (!is_scalar($value)) {
			return null;
		}

		return $value;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		if ($offset === 'APP_ROOT') {
			$this->cachedRootDirectory = null;
		}

		$this->memory[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		if ($offset === 'APP_ROOT') {
			$this->cachedRootDirectory = null;
		}

		unset($this->memory[$offset]);
	}

	public function asEnumerable(): GenericKeyedEnumerable
	{
		/** @var KeyedEnumerable<string, scalar|null> */
		return KeyedEnumerable::from($this->memory);
	}
}
