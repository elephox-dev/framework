<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Host\Contract\Environment;
use InvalidArgumentException;

class GlobalEnvironment implements Environment
{
	public const ENV_NAME = 'APP_ENV';

	public function getEnvironmentName(string $envName = self::ENV_NAME): string
	{
		return $this[$envName] ?? 'production';
	}

	public function isDevelopment(string $envName = self::ENV_NAME): bool
	{
		if ($this->offsetExists('APP_DEBUG')) {
			return (bool) $this['APP_DEBUG'];
		}

		return in_array($this->getEnvironmentName($envName), ['dev', 'local', 'debug', 'development']);
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		return isset($_ENV[$offset]) || getenv($offset) !== false;
	}

	public function offsetGet(mixed $offset): mixed
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		/** @psalm-suppress MixedReturnStatement */
		return $_ENV[$offset] ?? getenv($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		/** @psalm-suppress MixedOperand */
		putenv($offset . '=' . $value);
		/** @psalm-suppress MixedAssignment */
		$_ENV[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException('Environment offset must be a string');
		}

		putenv($offset);
		unset($_ENV[$offset]);
	}
}
