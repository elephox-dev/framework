<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Files\Contract\Directory;

abstract class AbstractEnvironment implements Contract\Environment
{
	public function getEnvironmentName(): string
	{
		if ($this->offsetExists('APP_ENV')) {
			return (string) $this->offsetGet('APP_ENV');
		}

		return 'production';
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
			return (bool) filter_var($this['APP_DEBUG'], (int) FILTER_VALIDATE_BOOL);
		}

		return in_array($this->getEnvironmentName(), ['dev', 'local', 'debug', 'development'], true);
	}
}
