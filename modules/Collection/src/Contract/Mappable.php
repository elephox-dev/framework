<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TIn
 */
interface Mappable
{
	/**
	 * @template TOut
	 *
	 * @param callable(TIn): TOut $callback
	 * @return GenericCollection<TOut>
	 */
	public function map(callable $callback): GenericCollection;
}
