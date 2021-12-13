<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Collection\Contract\ReadonlyMap;
use Elephox\Collection\Contract\ReadonlyList;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * @extends ReadonlyMap<HeaderName, ReadonlyList<string>>
 * @extends ArrayConvertible<non-empty-string, list<string>>
 */
interface ReadonlyHeaderMap extends ReadonlyMap, ArrayConvertible
{
	/**
	 * @param string|HeaderName $key
	 *
	 * @return bool
	 */
	#[Pure] public function has(mixed $key): bool;

	/**
	 * @param string|HeaderName $key
	 *
	 * @return ReadonlyList<string>
	 */
	#[Pure] public function get(mixed $key): mixed;

	#[Pure] public function asRequestHeaders(): RequestHeaderMap;

	#[Pure] public function asResponseHeaders(): ResponseHeaderMap;
}
