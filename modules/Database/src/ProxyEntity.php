<?php
declare(strict_types=1);

namespace Elephox\Database;

use Elephox\Database\Attributes\AttributeMetaData;
use Elephox\Database\Attributes\AttributeMetaDataBuilder;
use Elephox\Database\Attributes\Contract\DatabaseAttribute;
use Elephox\Database\Attributes\Generated;
use Elephox\Database\Attributes\Optional;
use ReflectionAttribute;
use ReflectionObject;
use ReflectionProperty;

/**
 * @template T of Contract\Entity
 */
final class ProxyEntity implements Contract\Entity
{
	/**
	 * @param T $entity
	 * @param bool $dirty
	 */
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

	public function __set(string $name, mixed $value)
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

	/**
	 * @return array<string, mixed>
	 */
	public function _proxyGetArrayCopy(): array
	{
		$data = [];

		$entityReflection = new ReflectionObject($this->entity);
		foreach ($entityReflection->getProperties() as $property) {
			$metaData = $this->_proxyGetAttributeMetaData($property);

			if (!$property->isInitialized($this->entity)) {
				if ($metaData->optional || $metaData->generated) {
					if ($property->hasDefaultValue()) {
						/** @var mixed */
						$data[$property->getName()] = $property->getDefaultValue();
					}

					continue;
				}

				throw new DatabaseException('Property ' . $property->getName() . ' is not initialized and is not optional or generated.');
			}

			/** @var mixed */
			$data[$property->getName()] = $property->getValue($this->entity);
		}

		return $data;
	}

	private function _proxyGetAttributeMetaData(ReflectionProperty $property): AttributeMetaData
	{
		$metaDataBuilder = new AttributeMetaDataBuilder();

		$attributes = $property->getAttributes(DatabaseAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
		foreach ($attributes as $attribute) {
			if ($attribute->getName() === Optional::class) {
				$metaDataBuilder->setIsOptional(true);
			} else if ($attribute->getName() === Generated::class) {
				$metaDataBuilder->setIsGenerated(true);
			}
		}

		return $metaDataBuilder->build();
	}
}
