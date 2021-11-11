<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

/**
 * Declares the implementation can be represented as a JSON string.
 */
interface JsonConvertible
{
	/**
	 * @param int $flags (optional) The same options you can pass to `json_encode`
	 *
	 * @return string|false Returns this object in its JSON representation or `false` on failure. This is the result of
	 * passing this object to `json_encode` with the given options.
	 *
	 * @see \json_encode()
	 */
	public function asJson(int $flags = 0): string|false;
}
