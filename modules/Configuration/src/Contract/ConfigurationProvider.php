<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\OOR\Str;

interface ConfigurationProvider
{
	public function set(string|Str $key, array|string|int|float|bool|null $value): void;

	public function tryGet(string|Str $key, array|string|int|float|bool|null &$value = null): bool;

	public function remove(string|Str $key): void;

	/**
	 * @return \Elephox\Collection\Contract\GenericEnumerable<string>
	 */
	public function getChildKeys(string|Str|null $path = null): GenericEnumerable;

	public function __serialize(): array;
	public function __unserialize(array $data): void;
}
