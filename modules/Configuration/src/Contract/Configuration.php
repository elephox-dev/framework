<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use ArrayAccess;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\OOR\Str;

/**
 * @extends ArrayAccess<string, string|int|float|bool|null>
 */
interface Configuration extends ArrayAccess
{
	/**
	 * @return GenericEnumerable<string>
	 */
	public function getChildKeys(string|Str|null $path = null): GenericEnumerable;

	/**
	 * @return GenericEnumerable<ConfigurationSection>
	 */
	public function getChildren(string|Str|null $path = null): GenericEnumerable;

	public function hasSection(string|Str $key): bool;

	public function getSection(string|Str $key): ConfigurationSection;

	/**
	 * @return scalar|null
	 */
	public function offsetGet(mixed $offset): string|int|float|bool|null;
}
