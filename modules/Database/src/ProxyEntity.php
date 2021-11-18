<?php
declare(strict_types=1);

namespace Elephox\Database;

use ReflectionClass;

final class ProxyEntity implements Contract\Entity
{
	/**
	 * @template T of Contract\Entity
	 *
	 * @param class-string<T> $class
	 * @param array $data
	 * @return T
	 */
	public static function hydrate(string $class, array $data): Contract\Entity
	{
		$entity = new $class;
		foreach ($data as $key => $value) {
			$entity->$key = $value;
		}

		return new self($entity);
	}

	public function __construct(
		private Contract\Entity $entity,
		private bool            $dirty = false,
	)
	{
	}

	public function __call(string $name, array $arguments)
	{
		if (str_starts_with($name, 'set')) {
			$this->dirty = true;
		}

		return $this->entity->{$name}(...$arguments);
	}

	public function __get(string $name)
	{
		return $this->entity->$name;
	}

	public function __set(string $name, $value)
	{
		$this->dirty = true;
		$this->entity->$name = $value;
	}

	public function __isset(string $name)
	{
		return isset($this->entity->$name);
	}

	public function getUniqueIdProperty(): string
	{
		return $this->entity->getUniqueIdProperty();
	}

	public function getUniqueId(): null|string|int
	{
		return $this->entity->getUniqueId();
	}

	public function _proxyIsDirty(): bool
	{
		return $this->dirty;
	}

	public function _proxyResetDirty(): void
	{
		$this->dirty = false;
	}

	public function _proxyGetEntity(): Contract\Entity
	{
		return $this->entity;
	}

	public function _proxyGetArrayCopy(): array
	{
		$data = [];

		$entityReflection = new ReflectionClass($this->entity);
		foreach ($entityReflection->getProperties() as $property) {
			$data[$property->getName()] = $property->getValue($this->entity);
		}

		return $data;
	}
}
