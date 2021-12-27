<?php
declare(strict_types=1);

namespace Elephox\PIE;

use InvalidArgumentException;

final class PIE
{
	/**
	 * @template T
	 *
	 * @param T $value
	 *
	 * @return GenericEnumerable<T, mixed>
	 */
	public static function from(mixed $value): GenericEnumerable
	{
		if (is_string($value)) {
			$value = explode('', $value);
		}

		if (is_array($value)) {
			if (array_is_list($value)) {
				return new Enumerable(new ListIterator($value));
			}

			return new Enumerable(new ArrayIterator($value));
		}

		if ($value instanceof GenericEnumerable) {
			return $value;
		}

		if ($value instanceof GenericIterator) {
			return new Enumerable($value);
		}

		throw new InvalidArgumentException('Value must be iterable');
	}
}
