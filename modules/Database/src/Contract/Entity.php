<?php
declare(strict_types=1);

namespace Elephox\Database\Contract;

/**
 * @template TId of string|int
 */
interface Entity
{
	/**
	 * @return TId
	 */
	public function getUniqueId(): string|int;
}
