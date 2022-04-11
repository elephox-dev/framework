<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use ArrayAccess;
use Elephox\Files\Contract\Directory;

/**
 * @extends ArrayAccess<string, scalar>
 * @property-read Directory $root
 * @property-read Directory $config
 * @property-read Directory $temp
 * @property-read bool $development
 */
interface Environment extends ArrayAccess
{
	public function loadFromEnvFile(?string $envName = null): void;

	public function getEnvironmentName(): string;

	public function getRoot(): Directory;

	public function getTemp(): Directory;

	public function getConfig(): Directory;

	public function isDevelopment(): bool;
}
