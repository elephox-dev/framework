<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<string, string|list<string>>
 */
interface HeaderMap extends GenericMap
{
	/**
	 * @param array<string, string|list<string>>|null $server
	 */
	public static function fromGlobals(?array $server = null): HeaderMap;
}
