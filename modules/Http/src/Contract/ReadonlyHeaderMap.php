<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Collection\Contract\GenericList;
use JetBrains\PhpStorm\Pure;

/**
 * @extends GenericMap<HeaderName, GenericList<string>>
 * @extends ArrayConvertible<non-empty-string, list<string>>
 */
interface ReadonlyHeaderMap extends GenericMap, ArrayConvertible
{
	/**
	 * @param string|HeaderName $key
	 *
	 * @return bool
	 */
	public function has(mixed $key): bool;

	/**
	 * @param string|HeaderName $key
	 *
	 * @return GenericList<string>
	 */
	public function get(mixed $key): mixed;

	public function asRequestHeaders(): RequestHeaderMap;

	public function asResponseHeaders(): ResponseHeaderMap;
}
