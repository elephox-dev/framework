<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;
use Elephox\Collection\OffsetNotFoundException;
use Elephox\Http\HeaderName;

/**
 * @extends GenericMap<string, list<string>>
 */
interface HeaderMap extends GenericMap
{
	/**
	 * @param array<string, list<string>>|null $server
	 */
	public static function fromGlobals(?array $server = null): self;

	/**
	 * @param string|HeaderName $key
	 * @param list<string> $value
	 */
	public function put(mixed $key, mixed $value): bool;

	/**
	 * @param string|HeaderName $key
	 *
	 * @return bool
	 */
	public function has(mixed $key): bool;

	/**
	 * @param string|HeaderName $key
	 *
	 * @return bool
	 */
	public function containsKey(mixed $key, ?callable $comparer = null): bool;

	/**
	 * @param string|HeaderName $key
	 *
	 * @return list<string>
	 *
	 * @throws OffsetNotFoundException
	 */
	public function get(mixed $key): array;

	/**
	 * @param string|HeaderName $key
	 */
	public function remove(mixed $key): bool;
}
