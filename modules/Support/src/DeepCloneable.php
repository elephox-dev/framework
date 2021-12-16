<?php
declare(strict_types=1);

namespace Elephox\Support;

use Exception;
use Iterator;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use SplObjectStorage;
use WeakMap;

trait DeepCloneable
{
	public function deepClone(): self
	{
		try {
			/** @var self */
			return $this->cloneRecursive($this);
		} catch (ReflectionException $e) {
			throw new RuntimeException('Cloning of ' . $this::class . ' failed.', previous: $e);
		}
	}

	private static array $clonedObjects = [];

	/**
	 * @throws ReflectionException
	 */
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

	/**
	 * @throws ReflectionException
	 */
	private function cloneArray(array $array): array
	{
		/**
		 * @var mixed $value
		 */
		foreach ($array as $key => $value) {
			/** @var array-key $clonedKey */
			$clonedKey = $this->cloneRecursive($key);
			/** @var mixed $clonedValue */
			$clonedValue = $this->cloneRecursive($value);

			/** @psalm-suppress MixedAssignment */
			$array[$clonedKey] = $clonedValue;
		}

		return $array;
	}

	/**
	 * @throws ReflectionException
	 * @throws Exception
	 */
	private function cloneObject(object $object): object
	{
		$hash = spl_object_hash($object);
		if (isset(self::$clonedObjects[$hash])) {
			/** @var object */
			return self::$clonedObjects[$hash];
		}

		$reflection = new ReflectionClass($object);
		$clone = $reflection->newInstance();
		self::$clonedObjects[$hash] = $clone;

		if ($object instanceof WeakMap) {
			/** @var WeakMap $clone */

			/** @var Iterator<object, mixed> $iterator */
			$iterator = $object->getIterator();

			$iterator->rewind();

			while ($iterator->valid()) {
				/** @var object $key */
				$key = $iterator->key();
				/** @var mixed $value */
				$value = $iterator->current();

				/** @var object $clonedKey */
				$clonedKey = $this->cloneRecursive($key);
				/** @var mixed $clonedValue */
				$clonedValue = $this->cloneRecursive($value);

				$clone->offsetSet($clonedKey, $clonedValue);
				$iterator->next();
			}

			return $clone;
		}

		if ($object instanceof SplObjectStorage) {
			/** @var SplObjectStorage $clone */

			$iterator = $object;
			while ($iterator->valid()) {
				/** @var object $key */
				$key = $iterator->current();
				/** @var mixed $value */
				$value = $object->offsetGet($key);

				/** @var object $clonedKey */
				$clonedKey = $this->cloneRecursive($key);
				/** @var mixed $clonedValue */
				$clonedValue = $this->cloneRecursive($value);

				$clone->offsetSet($clonedKey, $clonedValue);
				$iterator->next();
			}

			return $clone;
		}

		do {
			$properties = $reflection->getProperties();
			foreach ($properties as $property) {
				if ($property->isStatic()) {
					continue;
				}

				$property->setValue($clone, $this->cloneRecursive($property->getValue($object)));
			}
		} while ($reflection = $reflection->getParentClass());

		return $clone;
	}
}
