<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<array-key, mixed>
 */
interface SessionMap extends GenericMap
{
	public static function fromGlobals(?array $session = null, bool $recreate = false): ?SessionMap;
}
