<?php
declare(strict_types=1);

namespace Elephox\Entity;

use Elephox\Collection\IsEnumerable;
use Elephox\Entity\Contract\EntitySet;
use Elephox\Support\DeepCloneable;

/**
 * @template T
 *
 * @implements EntitySet<T>
 */
abstract class AbstractEntitySet implements EntitySet
{
	use DeepCloneable, IsEnumerable;

	abstract public function getEntityClass(): string;

	public function getIterator(): EntitySetIterator
	{
		return new EntitySetIterator();
	}

	public function add(mixed $value): bool
	{
		// TODO: Implement add() method.
	}

	public function remove(mixed $value): bool
	{
		// TODO: Implement remove() method.
	}

	public function removeBy(callable $predicate): bool
	{
		// TODO: Implement removeBy() method.
	}
}
