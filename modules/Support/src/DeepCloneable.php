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
		$iterator = null;
		if ($object instanceof WeakMap) {
			/** @var Iterator<object, mixed> $iterator */
			$iterator = $object->getIterator();
			/** @var WeakMap $clone */
			$clone = $reflection->newInstance();
		} else if ($object instanceof SplObjectStorage) {
			$iterator = $object;
			/** @var SplObjectStorage $clone */
			$clone = $reflection->newInstance();
		} else if ($reflection->isCloneable()) {
			$clone = clone $object;
			self::$clonedObjects[$hash] = $clone;
		} else {
			$clone = $reflection->newInstance();
		}

		if ($iterator) {
			/** @var WeakMap|SplObjectStorage $clone */

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

		$properties = $reflection->getProperties();
		foreach ($properties as $property) {
			$property->setValue($clone, $this->cloneRecursive($property->getValue($object)));
		}

		return $clone;
	}
}
