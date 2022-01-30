<?php
declare(strict_types=1);

namespace Elephox\Entity;

use CallbackFilterIterator;
use Elephox\Collection\IsEnumerable;
use Elephox\Collection\Iterator\SplObjectStorageIterator;
use Elephox\Entity\Contract\EntitySet;
use Elephox\Support\DeepCloneable;
use SplObjectStorage;

/**
 * @template T
 *
 * @implements EntitySet<T>
 */
abstract class AbstractEntitySet implements EntitySet
{
	/**
	 * @use IsEnumerable<T>
	 */
	use IsEnumerable {
		contains as enumerableContains;
	}
	use DeepCloneable;

	abstract public function getEntityClass(): string;

	/** @var SplObjectStorage<T, Contract\ChangeHistory> */
	private SplObjectStorage $storage;

	public function getIterator(): CallbackFilterIterator
	{
		return new CallbackFilterIterator(new SplObjectStorageIterator($this->storage), function (Contract\ChangeHistory $changeHistory) {
			return $changeHistory->last()?->getAction() !== ChangeAction::Deleted;
		});
	}

	public function add(mixed $value): bool
	{
		if (!$value instanceof EntityDecorator) {
			$decorated = new EntityDecorator($value);
		} else {
			$decorated = $value;
		}

		if ($this->storage->contains($decorated)) {
			return false;
		}

		$history = $decorated->_decorator_getChangeHistory();

		if ($history->isEmpty()) {
			$history->add(new ChangeUnit(ChangeAction::Created, null, null, $value));
		}

		$this->storage->attach($decorated, $history);

		return true;
	}

	public function remove(mixed $value): bool
	{
		if (!$value instanceof EntityDecorator) {
			$decorated = new EntityDecorator($value);
		} else {
			$decorated = $value;
		}

		if (!$this->storage->contains($decorated)) {
			return false;
		}

		$history = $decorated->_decorator_getChangeHistory();
		$history->add(new ChangeUnit(ChangeAction::Deleted, null, $value, null));

		return true;
	}

	public function removeBy(callable $predicate): bool
	{
		$anyRemoved = false;

		foreach ($this->getIterator() as $entityDecorator) {
			$entity = $entityDecorator->_decorator_getEntity();
			if ($entity !== null && $predicate($entity)) {
				$anyRemoved = $anyRemoved || $this->remove($entity);
			}
		}

		return $anyRemoved;
	}
}
