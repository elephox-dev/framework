<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Files\Contract\Directory;

abstract class AbstractEnvironment implements Contract\Environment
{
	public function environmentName(): string
	{
		if ($this->offsetExists('APP_ENV')) {
			return (string) $this->offsetGet('APP_ENV');
		}

		return 'production';
	}

	public function temp(): Directory
	{
		return $this->root()->directory('tmp');
	}

	public function config(): Directory
	{
		return $this->root();
	}

	public function isDevelopment(): bool
	{
		if ($this->offsetExists('APP_DEBUG')) {
			return (bool) filter_var($this['APP_DEBUG'], (int) FILTER_VALIDATE_BOOL);
		}

		return in_array($this->environmentName(), ['dev', 'local', 'debug', 'development'], true);
	}
}
