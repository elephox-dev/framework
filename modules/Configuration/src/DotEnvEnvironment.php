<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;

abstract class DotEnvEnvironment extends AbstractEnvironment
{
	public function getDotEnvFileName(bool $local = false, ?string $envName = null): FileContract
	{
		$envFileName = '.env';
		if ($envName !== null) {
			$envFileName .= '.' . $envName;
		}

		if ($local) {
			$envFileName .= '.local';
		}

		return new File($envFileName);
	}
}
