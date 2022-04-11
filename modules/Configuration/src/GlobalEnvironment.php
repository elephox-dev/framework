<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Dotenv\Dotenv;
use Elephox\Files\Directory;
use InvalidArgumentException;
use RuntimeException;

class GlobalEnvironment implements Contract\Environment
{
	protected ?Directory $rootDirectory = null;

	public function loadFromEnvFile(?string $envName = null): void
	{
		$envFile = '.env';
		if ($envName !== null) {
			$envFile .= '.' . $envName;
		}

		$dotenv = Dotenv::createImmutable($this->getRootDirectory()->getPath(), $envFile);
		$dotenv->safeLoad();

		$dotenvLocal = Dotenv::createImmutable($this->getRootDirectory()->getPath(), $envFile . '.local');
		$dotenvLocal->safeLoad();
	}

	public function getEnvironmentName(): string
	{
		$env = $this['APP_ENV'];
		if (is_string($env)) {
			return $env;
		}

		return 'production';
	}

	public function getRootDirectory(): Directory
	{
		if ($this->rootDirectory !== null) {
			return $this->rootDirectory;
		}

		if (defined('APP_ROOT')) {
			$dir = new Directory(APP_ROOT);
		} else if ($this->offsetExists('APP_ROOT')) {
			$dir = new Directory((string)$this['APP_ROOT']);
		} else {
			$cwd = getcwd();
			if (!$cwd) {
				throw new RuntimeException('Cannot get current working directory');
			}

			$dir = new Directory($cwd);
		}

		$this->rootDirectory = $dir;

		return $dir;
	}

	public function isDevelopment(): bool
	{
		if ($this->offsetExists('APP_DEBUG')) {
			return (bool) $this['APP_DEBUG'];
		}

		return in_array($this->getEnvironmentName(), ['dev', 'local', 'debug', 'development'], true);
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
	 * @psalm-suppress MixedInferredReturnType
	 */
	public function offsetGet(mixed $offset): mixed
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		/** @psalm-suppress MixedReturnStatement */
		return $_ENV[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		$_ENV[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		unset($_ENV[$offset]);
	}
}
