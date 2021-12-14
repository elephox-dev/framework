<?php
declare(strict_types=1);

namespace Elephox\Support;

use ReflectionClass;
use SplObjectStorage;
use WeakMap;

trait DeepCloneable
{
	public function deepClone(): self
	{
		/** @var self */
		return $this->cloneRecursive($this);
	}

	private array $clonedObjects = [];

	private function cloneRecursive(mixed $value): mixed
	{
		if (is_resource($value)) {
			return $value;
		}

		if (is_array($value)) {
			return $this->cloneArray($value);
		}

		if (!is_object($value)) {
			return $value;
		}

		return $this->cloneObject($value);
	}

	private function cloneArray(array $array): array
	{
		/**
		 * @var mixed $value
		 */
		foreach ($array as $key => $value) {
			/** @var mixed */
			$array[$this->cloneRecursive($key)] = $this->cloneRecursive($value);
		}

		return $array;
	}

	private function cloneObject(object $object): object
	{
		$hash = spl_object_hash($object);
		if (isset($this->clonedObjects[$hash])) {
			/** @var object */
			return $this->clonedObjects[$hash];
		}

		$reflection = new ReflectionClass($object);
		if ($object instanceof WeakMap || $object instanceof SplObjectStorage) {
			$iterator = $object->getIterator();
			$iterator->rewind();

			$clone = $reflection->newInstance();

			while ($iterator->valid()) {
				$key = $iterator->key();
				$value = $iterator->current();
				$clone->offsetSet($this->cloneRecursive($key), $this->cloneRecursive($value));
				$iterator->next();
			}

			return $clone;
		}

		if ($reflection->isCloneable()) {
			$clone = clone $object;
			$this->clonedObjects[$hash] = $clone;
		} else {
			$clone = $reflection->newInstanceWithoutConstructor();
		}

		$properties = $reflection->getProperties();
		foreach ($properties as $property) {
			$property->setValue($clone, $this->cloneRecursive($property->getValue($object)));
		}

		/** @var object */
		return $clone;
	}
}
