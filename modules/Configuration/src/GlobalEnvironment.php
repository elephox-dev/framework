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

class GlobalEnvironment extends DotEnvEnvironment
{
	use TransparentGetterSetter;

	protected ?Directory $cachedRootDirectory = null;

	public function loadFromEnvFile(?string $envName = null, bool $local = false, bool $overwriteExisting = true): void
	{
		$root = $this->root()->path();
		$envFile = $this->getDotEnvFileName($local, $envName);

		if ($overwriteExisting) {
			$dotenv = Dotenv::createMutable($root, $envFile);
		} else {
			$dotenv = Dotenv::createImmutable($root, $envFile);
		}

		$dotenv->safeLoad();
	}

	public function root(): Directory
	{
		if ($this->cachedRootDirectory !== null) {
			return $this->cachedRootDirectory;
		}

		if (defined('APP_ROOT')) {
			$dir = new Directory(APP_ROOT);
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

		return isset($_ENV[$offset]);
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

		$value = $_ENV[$offset] ?? null;

		if (!is_scalar($value)) {
			return null;
		}

		return $value;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		if ($offset === 'APP_ROOT') {
			$this->cachedRootDirectory = null;
		}

		$_ENV[$offset] = $value;
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

		unset($_ENV[$offset]);
	}

	public function asEnumerable(): GenericKeyedEnumerable
	{
		/** @var KeyedEnumerable<string, scalar|null> */
		return KeyedEnumerable::from($_ENV);
	}
}
