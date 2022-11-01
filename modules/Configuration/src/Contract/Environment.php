<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use ArrayAccess;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\Directory;
use Elephox\Files\Contract\File;

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
	public function loadFromEnvFile(File $envFile, bool $overwriteExisting = true): void;

	public function environmentName(): string;

	public function root(): Directory;

	public function temp(): Directory;

	public function config(): Directory;

	public function isDevelopment(): bool;

	/**
	 * @return GenericKeyedEnumerable<string, scalar|null>
	 */
	public function asEnumerable(): GenericKeyedEnumerable;
}
