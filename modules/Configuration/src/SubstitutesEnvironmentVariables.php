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
		preg_match_all('/(?<!\$)\$\{([^\}]+)\}/m', $value, $matches);

		foreach ($matches[1] as $match) {
			$substitute = $this->getEnvSubstitute($match);
			if ($substitute !== null) {
				$value = str_replace('${' . $match . '}', $substitute, $value);
			}
		}

		return $value;
	}
}
