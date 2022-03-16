<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Host\Contract\Environment;
use InvalidArgumentException;

class GlobalEnvironment implements Environment
{
	private const ENV_VAR_NAME = 'APP_ENV';

	public function isDebug(string $envName = self::ENV_VAR_NAME): bool
	{
		return in_array($this[$envName], ['dev', 'local', 'debug', 'development']);
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
