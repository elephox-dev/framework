<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use Elephox\Collection\Contract\GenericEnumerable;
use Stringable;

interface ConfigurationProvider
{
	public function set(string|Stringable $key, array|string|int|float|bool|null $value): void;

	public function tryGet(string|Stringable $key, array|string|int|float|bool|null &$value = null): bool;

	public function remove(string|Stringable $key): void;

	/**
	 * @param null|string|Stringable $path
	 *
	 * @return GenericEnumerable<string>
	 */
	public function getChildKeys(string|Stringable|null $path = null): GenericEnumerable;

	public function __serialize(): array;
	public function __unserialize(array $data): void;
}
