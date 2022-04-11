<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Stringable;

trait SubstitutesEnvironmentVariables
{
	protected function getEnvSubstitute(string $name): ?string
	{
		if (array_key_exists($name, $_ENV)) {
			$value = $_ENV[$name];
			$type = get_debug_type($value);

			return match (true) {
				$type === 'null' => 'null',
				$type === 'bool' => $value ? 'true' : 'false',
				$type === 'int',
				$type === 'float',
				$type === 'string',
				$value instanceof Stringable => (string) $value,
				default => $type,
			};
		}

		return null;
	}

	protected function substituteEnvironmentVariables(string $value): string
	{
		// Replace unescaped environment variables with their values (${ENV_VAR} => value)
		$value = preg_replace_callback('/(?<!\$)\${([^}]+)}/m', function (array $match) {
			$substitute = $this->getEnvSubstitute($match[1]);

			// Replaced nested substitutions
			return $substitute !== null ? $this->substituteEnvironmentVariables($substitute) : $match[0];
		}, $value);

		// Replace escaped variables with unescaped ones ($${ENV_VAR} => ${ENV_VAR})
		return preg_replace_callback('/\$(\${[^}]+})/m', static fn (array $match) => $match[1], $value);
	}
}
