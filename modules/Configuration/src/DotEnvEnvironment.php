<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;

abstract class DotEnvEnvironment extends AbstractEnvironment
{
	public function getDotEnvFileName(bool $local = false, ?string $envName = null): FileContract
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
