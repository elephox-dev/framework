<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Http\Contract\SessionMap;

/**
 * @extends ArrayMap<array-key, mixed>
 */
class FakeSessionMap extends ArrayMap implements SessionMap
{
	public static function fromGlobals(?array $session = null, bool $recreate = false): ?SessionMap
	{
		return new self($session ?? []);
	}
}
