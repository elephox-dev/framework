<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use JetBrains\PhpStorm\Pure;

/**
 * @template T
 */
interface Filterable
{
	/**
	 * @param null|callable(T): bool $filter
	 * @return T|null
	 */
	#[Pure] public function first(?callable $filter = null): mixed;

	/**
	 * @param null|callable(T): bool $filter
	 * @return bool
	 */
	#[Pure] public function any(?callable $filter = null): bool;

	/**
	 * @param callable(T): bool $filter
	 * @return GenericCollection<T>
	 */
	#[Pure] public function where(callable $filter): GenericCollection;

	/**
	 * @param T $value
	 * @return bool
	 */
	#[Pure] public function contains(mixed $value): bool;
}
