<?php
declare(strict_types=1);

namespace Elephox\PIE;

use InvalidArgumentException;
use Iterator;

final class PIE
{
	/**
	 * @template T
	 *
	 * @param T $value
	 *
	 * @return GenericEnumerable<mixed, T>
	 */
	public static function from(mixed $value): GenericEnumerable
	{
		if (is_string($value)) {
			$value = str_split($value);
		}
		if (is_array($value)) {
			return new Enumerable(new ArrayIterator($value));
		}

		if (is_object($value)) {
			if ($value instanceof Iterator) {
				return new Enumerable($value);
			}

			if ($value instanceof GenericEnumerable) {
				return $value;
			}
		}

		throw new InvalidArgumentException('Value must be iterable');
	}
}
