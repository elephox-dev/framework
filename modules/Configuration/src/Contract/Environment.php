<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use ArrayAccess;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\Directory;

/**
 * @extends ArrayAccess<string, scalar|null>
 *
 * @property Directory $root
 * @property Directory $config
 * @property Directory $temp
 * @property bool $development
 */
interface Environment extends ArrayAccess
{
	public function loadFromEnvFile(?string $envName = null, bool $local = false, bool $overwriteExisting = true): void;

	public function getEnvironmentName(): string;

	public function getRoot(): Directory;

	public function getTemp(): Directory;

	public function getConfig(): Directory;

	public function isDevelopment(): bool;

	/**
	 * @return GenericKeyedEnumerable<string, scalar|null>
	 */
	public function asEnumerable(): GenericKeyedEnumerable;
}
