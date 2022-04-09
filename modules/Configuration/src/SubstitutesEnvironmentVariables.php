<?php
declare(strict_types=1);

namespace Elephox\Configuration;

trait SubstitutesEnvironmentVariables
{
	protected function getEnvSubstitute(string $name): ?string
	{
		if (isset($_ENV[$name])) {
			return (string) $_ENV[$name];
		}

		return null;
	}

	protected function substituteEnvironmentVariables(string $value): string
	{
		// Replace unescaped environment variables with their values (${ENV_VAR} => value)
		$value = preg_replace_callback('/(?<!\$)\${([^}]+)}/m', function (array $match) {
			$substitute = $this->getEnvSubstitute($match[1]);

			return $substitute ?? $match[0];
		}, $value);

		// Replace escaped variables with unescaped ones ($${ENV_VAR} => ${ENV_VAR})
		return preg_replace_callback('/\$(\${[^}]+})/m', static fn (array $match) => $match[1], $value);
	}
}
