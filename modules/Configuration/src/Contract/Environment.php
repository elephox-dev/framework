<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use ArrayAccess;
use Elephox\Files\Contract\Directory;

/**
 * @extends ArrayAccess<string, scalar>
 */
interface Environment extends ArrayAccess
{
	public function getEnvironmentName(): string;

	public function getRootDirectory(): Directory;

	public function isDevelopment(): bool;
}
