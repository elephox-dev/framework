<?php
declare(strict_types=1);

namespace Elephox\PIE;

/**
 * @template TKey
 * @template TElement
 *
 * @extends GenericEnumerable<TElement>
 */
interface GenericGrouping extends GenericEnumerable
{
	/**
	 * @return TKey
	 */
	public function getKey(): mixed;
}
