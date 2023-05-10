<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

use Stringable;

/**
 * @template TContent
 */
interface QueryValue extends Stringable
{
	/**
	 * @return TContent
	 */
	public function getValue(): mixed;
}
