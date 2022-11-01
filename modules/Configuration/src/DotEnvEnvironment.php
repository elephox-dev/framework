<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Files\File;

abstract class DotEnvEnvironment extends AbstractEnvironment
{
	public function getDotEnvFileName(bool $local = false, ?string $envName = null): File
	{
		$envFile = '.env';
		if ($envName !== null) {
			$envFile .= '.' . $envName;
		}

		if ($local) {
			$envFile .= '.local';
		}

		return new File($envFile);
	}
}
