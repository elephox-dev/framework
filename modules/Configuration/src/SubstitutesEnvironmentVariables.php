<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Collection\KeyedEnumerable;
use Stringable;

trait SubstitutesEnvironmentVariables
{
	protected function getEnvSubstitute(string $name): ?string
	{
		if (!empty($name) && array_key_exists($name, $_ENV)) {
			/** @var mixed $value */
			$value = $_ENV[$name];
			$type = get_debug_type($value);

			return match (true) {
				$type === 'null' => 'null',
				$type === 'bool' => $value ? 'true' : 'false',
				$type === 'int',
				$type === 'float',
				$type === 'string',
				$value instanceof Stringable => /** @var (int|float|string|Stringable) $value */ (string)$value,
				default => $type,
			};
		}

		return null;
	}

	protected function substituteEnvironmentVariables(string|Stringable $value): string
	{
		// Replace unescaped environment variables with their values (${ENV_VAR} => value)
		/** @var string $value */
		$value = preg_replace_callback('/(?<!\$)\${([^}]+)}/m', function (array $match) {
			$substitute = $this->getEnvSubstitute($match[1]);

			// Replaced nested substitutions
			return $substitute !== null ? $this->substituteEnvironmentVariables($substitute) : $match[0];
		}, (string)$value);

		// Replace escaped variables with unescaped ones ($${ENV_VAR} => ${ENV_VAR})
		/** @var string */
		return preg_replace_callback('/\$(\${[^}]+})/m', static fn(array $match) => $match[1], $value);
	}

	protected function substituteEnvironmentVariablesRecursive(iterable $values): iterable
	{
		/**
		 * @var mixed $key
		 * @var mixed $value
		 */
		foreach ($values as $key => $value) {
			if (is_iterable($value)) {
				yield $key => KeyedEnumerable::from($this->substituteEnvironmentVariablesRecursive($value))->toArray();
			} elseif (is_string($value) || is_a($value, Stringable::class)) {
				yield $key => $this->substituteEnvironmentVariables($value);
			}
		}
	}
}
