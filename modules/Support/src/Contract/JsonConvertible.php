<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

use JsonException;

/**
 * Declares the implementation can be represented as a JSON string.
 */
interface JsonConvertible
{
	/**
	 * @param int $flags (optional) The same options you can pass to `json_encode`
	 *
	 * @return string Returns this object in its JSON representation. Throws a JsonException on failure. This is the
	 * result of passing this object to `json_encode` with the given options.
	 *
	 * @throws JsonException
	 * @see json_encode()
	 */
	public function toJson(int $flags = 0): string;
}
