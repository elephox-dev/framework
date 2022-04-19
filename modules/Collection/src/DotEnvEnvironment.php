<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Dotenv\Dotenv;
use Elephox\Configuration\AbstractEnvironment;

abstract class DotEnvEnvironment extends AbstractEnvironment
{
	abstract protected function storeDotEnvContents(array $envFile, array $localEnvFile): void;

	public function loadFromEnvFile(?string $envName = null): void
	{
		$envFile = '.env';
		if ($envName !== null) {
			$envFile .= '.' . $envName;
		}

		$dotenv = Dotenv::createImmutable($this->getRoot()->getPath(), $envFile);
		$dotenvLocal = Dotenv::createImmutable($this->getRoot()->getPath(), $envFile . '.local');

		$this->storeDotEnvContents($dotenv->safeLoad(), $dotenvLocal->safeLoad());
	}

	public function watchEnvFile(callable $callback): void
	{
		// TODO: implement watch env file
	}
}
