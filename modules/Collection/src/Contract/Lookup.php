<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TElement
 *
 * @extends GenericEnumerable<Grouping<TKey, TElement>>
 */
interface Lookup extends GenericEnumerable
{
	/**
	 * @param TKey $key
	 *
	 * @return GenericEnumerable<TElement>
	 */
	public function get(mixed $key): mixed;

	/**
	 * @param TKey $key
	 */
	public function containsKey(mixed $key): bool;
}
