<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use ArrayAccess;
use Elephox\Collection\Contract\GenericEnumerable;
use Stringable;

/**
 * @extends ArrayAccess<string, array|string|int|float|bool|null>
 */
interface Configuration extends ArrayAccess
{
	/**
	 * @param null|string|Stringable $path
	 *
	 *@return GenericEnumerable<string>
	 */
	public function getChildKeys(string|Stringable|null $path = null): GenericEnumerable;

	/**
	 * @param null|string|Stringable $path
	 *
	 *@return GenericEnumerable<ConfigurationSection>
	 */
	public function getChildren(string|Stringable|null $path = null): GenericEnumerable;

	public function hasSection(string|Stringable $key): bool;

	public function getSection(string|Stringable $key): ConfigurationSection;

	public function offsetGet(mixed $offset): array|string|int|float|bool|null;
}
