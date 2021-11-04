<?php
declare(strict_types=1);

namespace Philly\Collection\Contract;

/**
 * @template T
 */
interface Filterable
{
	/**
	 * @param null|callable(T): bool $filter
	 * @return T|null
	 */
	public function first(?callable $filter = null): mixed;

	/**
	 * @param null|callable(T): bool $filter
	 * @return bool
	 */
	public function any(?callable $filter = null): bool;

	/**
	 * @param callable(T): bool $filter
	 * @return GenericCollection<T>
	 */
	public function where(callable $filter): GenericCollection;
}
