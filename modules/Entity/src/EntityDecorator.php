<?php
declare(strict_types=1);

namespace Elephox\Entity;

use WeakReference;

/**
 * @template T of object
 */
class EntityDecorator
{
	/**
	 * @var WeakReference<T>
	 */
	private WeakReference $entityReference;

	private Contract\ChangeHistory $changeHistory;

	/**
	 * @param T $entity
	 */
	public function __construct(
		object $entity,
	)
	{
		$this->entityReference = WeakReference::create($entity);
		$this->changeHistory = new ChangeHistory();
	}

	public function __set(string $property, mixed $value): void
	{
		$entity = $this->entityReference->get();
		if ($entity === null) {
			return;
		}

		$unit = new ChangeUnit(ChangeAction::Updated, $property, $entity->$property, $value);
		$this->changeHistory->add($unit);

		$entity->$property = $value;
	}

	public function __get(string $property): mixed
	{
		return $this->entityReference->get()->$property;
	}

	public function __call(string $method, array $params): mixed
	{
		return $this->entityReference->get()?->$method(...$params);
	}

	public function __isset(string $property): bool
	{
		return isset($this->entityReference->get()->$property);
	}

	public function _decorator_getChangeHistory(): Contract\ChangeHistory
	{
		return $this->changeHistory;
	}
}
