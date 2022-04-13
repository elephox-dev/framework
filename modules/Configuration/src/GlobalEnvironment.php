<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Dotenv\Dotenv;
use Elephox\Files\Directory;
use Elephox\Support\TransparentGetterSetter;
use InvalidArgumentException;
use RuntimeException;

class GlobalEnvironment implements Contract\Environment
{
	use TransparentGetterSetter;

	protected ?Directory $cachedRootDirectory = null;

	public function loadFromEnvFile(?string $envName = null): void
	{
		$envFile = '.env';
		if ($envName !== null) {
			$envFile .= '.' . $envName;
		}

		$dotenv = Dotenv::createImmutable($this->getRoot()->getPath(), $envFile);
		$dotenv->safeLoad();

		$dotenvLocal = Dotenv::createImmutable($this->getRoot()->getPath(), $envFile . '.local');
		$dotenvLocal->safeLoad();
	}

	public function getEnvironmentName(): string
	{
		/** @var mixed $env */
		$env = $this['APP_ENV'];
		if (is_string($env)) {
			return $env;
		}

		return 'production';
	}

	public function getRoot(): Directory
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

	public function getTemp(): Directory
	{
		return $this->getRoot()->getDirectory('tmp');
	}

	public function getConfig(): Directory
	{
		return $this->getRoot();
	}

	public function isDevelopment(): bool
	{
		if ($this->offsetExists('APP_DEBUG')) {
			return filter_var($this['APP_DEBUG'], FILTER_VALIDATE_BOOL);
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
