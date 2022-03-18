<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use ArrayAccess;
use Elephox\DI\Contract\NotContainerSerializable;

/**
 * @extends ArrayAccess<string, scalar>
 */
interface Environment extends ArrayAccess, NotContainerSerializable
{
	public function getEnvironmentName(): string;

	public function isDevelopment(): bool;
}
