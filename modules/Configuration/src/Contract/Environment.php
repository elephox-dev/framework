<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use ArrayAccess;

/**
 * @extends ArrayAccess<string, scalar>
 */
interface Environment extends ArrayAccess
{
	public function getEnvironmentName(): string;

	public function isDevelopment(): bool;
}
