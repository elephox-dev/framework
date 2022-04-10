<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Exception;
use Iterator;
use ReflectionException;
use ReflectionObject;
use RuntimeException;
use SplObjectStorage;
use Throwable;
use UnitEnum;
use WeakMap;

trait DeepCloneable
{
	public function deepClone(): static
	{
		try {
			/** @var array<string, object> $cloneStorage */
			$cloneStorage = [];

			/** @var static */
			return self::cloneRecursive($this, $cloneStorage);
		} catch (Throwable $e) {
			throw new RuntimeException('Cloning of ' . $this::class . ' failed.', previous: $e);
		}
	}

	/**
	 * @param array<string, object> $cloneStorage
	 *
	 * @throws ReflectionException
	 */
	private static function cloneRecursive(mixed $value, array &$cloneStorage): mixed
	{
		if (is_resource($value)) {
			return $value;
		}

		if (is_array($value)) {
			return self::cloneArray($value, $cloneStorage);
		}

		if (!is_object($value)) {
			return $value;
		}

		if ($value instanceof UnitEnum) {
			return $value;
		}

		return self::cloneObject($value, $cloneStorage);
	}

	/**
	 * @param array<string, object> $cloneStorage
	 *
	 * @throws ReflectionException
	 *
	 * @psalm-suppress UnusedParam
	 */
	private static function cloneArray(array $array, array &$cloneStorage): array
	{
		$clonedArray = [];

		/**
		 * @var mixed $value
		 */
		foreach ($array as $key => $value) {
			/** @var array-key $clonedKey */
			$clonedKey = self::cloneRecursive($key, $cloneStorage);

			$clonedValue = self::cloneRecursive($value, $cloneStorage);

			/** @psalm-suppress MixedAssignment */
			$clonedArray[$clonedKey] = $clonedValue;
		}

		return $clonedArray;
	}

	/**
	 * @param array<string, object> $cloneStorage
	 *
	 * @throws ReflectionException
	 * @throws Exception
	 */
	private static function cloneObject(object $object, array &$cloneStorage): ?object
	{
		$hash = spl_object_hash($object);
		if (isset($cloneStorage[$hash])) {
			/** @var object */
			return $cloneStorage[$hash];
		}

		$reflection = new ReflectionObject($object);
		if ($reflection->hasMethod('__clone')) {
			$clone = clone $object;
		} else {
			$clone = $reflection->newInstance();
		}
		$cloneStorage[$hash] = &$clone;

		if ($object instanceof WeakMap) {
			/** @var Iterator<object, mixed> $iterator */
			$iterator = $object->getIterator();

			while ($iterator->valid()) {
				/** @var object $key */
				$key = $iterator->key();

				$value = $iterator->current();

				// don't clone the key since it is a weak reference to an object and a cloned object
				// would have no references to it, causing it to be garbage collected.

				$clonedValue = self::cloneRecursive($value, $cloneStorage);

				/**
				 * @psalm-suppress UnnecessaryVarAnnotation
				 *
				 * @var WeakMap $clone
				 */
				$clone->offsetSet($key, $clonedValue);
				$iterator->next();
			}

			return $clone;
		}

		if ($object instanceof SplObjectStorage) {
			$object->rewind();
			while ($object->valid()) {
				$key = $object->current();

				$value = $object->offsetGet($key);

				/** @var object $clonedKey */
				$clonedKey = self::cloneRecursive($key, $cloneStorage);

				$clonedValue = self::cloneRecursive($value, $cloneStorage);

				/** @var SplObjectStorage $clone */
				$clone->offsetSet($clonedKey, $clonedValue);
				$object->next();
			}

			return $clone;
		}

		$properties = $reflection->getProperties();
		foreach ($properties as $property) {
			if ($property->isStatic()) {
				continue;
			}

			$propertyValue = $property->getValue($object);

			$clonedPropertyValue = self::cloneRecursive($propertyValue, $cloneStorage);
			$property->setValue($clone, $clonedPropertyValue);
		}

		return $clone;
	}
}
