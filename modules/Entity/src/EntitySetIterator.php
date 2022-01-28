<?php
declare(strict_types=1);

namespace Elephox\Entity;

use Iterator;
use SplObjectStorage;

/**
 * @template T
 *
 * @implements Iterator<int, T>
 */
class EntitySetIterator implements Iterator
{
	private int $position = 0;

	/**
	 * @param SplObjectStorage<T, Contract\ChangeHistory> $entitiesWithHistory
	 */
	public function __construct(
		private SplObjectStorage $entitiesWithHistory,
	)
	{
	}

	public function current(): object
	{
		return $this->entitiesWithHistory->current();
	}

	public function next(): void
	{
		$this->entitiesWithHistory->next();
		if (!$this->entitiesWithHistory->valid()) {
			return;
		}

		/** @var ChangeAction $lastChange */
		$lastChange = $this->entitiesWithHistory->getInfo()->last()->getAction();
		if ($lastChange === ChangeAction::Deleted) {
			$this->next();
			return;
		}

		$this->position++;
	}

	public function key(): int
	{
		return $this->position;
	}

	public function valid(): bool
	{
		return $this->entitiesWithHistory->valid();
	}

	public function rewind(): void
	{
		$this->entitiesWithHistory->rewind();
		$this->position = 0;
	}
}
