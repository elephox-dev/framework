<?php
declare(strict_types=1);

namespace Philly\Collection\Contract;

/**
 * @template TIn
 */
interface Mappable
{
	/**
	 * @template TOut
	 *
	 * @param callable(TIn): TOut $callback
	 * @return \Philly\Collection\Contract\GenericCollection<TOut>
	 */
	public function map(callable $callback): GenericCollection;
}
