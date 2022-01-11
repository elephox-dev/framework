<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\Comparable;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

final class DefaultEqualityComparer
{
	#[Pure] public static function equals(mixed $a, mixed $b): bool
	{
		if ($a instanceof Comparable && is_object($b)) {
			return $a->compareTo($b) === 0;
		}

		if ($b instanceof Comparable && is_object($a)) {
			return $b->compareTo($a) === 0;
		}

		/** @noinspection TypeUnsafeComparisonInspection */
		return $a == $b;
	}

	#[Pure] public static function same(mixed $a, mixed $b): bool
	{
		if (is_object($a) && is_object($b)) {
			if ($a instanceof Comparable) {
				return $a->compareTo($b) === 0;
			}

			if ($b instanceof Comparable) {
				return $b->compareTo($a) === 0;
			}

			return spl_object_hash($a) === spl_object_hash($b);
		}

		return $a === $b;
	}

	#[Pure] public static function compare(mixed $a, mixed $b): int
	{
		if ($a instanceof Comparable && is_object($b)) {
			return $a->compareTo($b);
		}

		if ($b instanceof Comparable && is_object($a)) {
			return $b->compareTo($a);
		}

		return $a <=> $b;
	}

	/**
	 * @template TCallable as callable(...mixed): (bool|int)
	 *
	 * @param TCallable $comparer
	 * @return callable(...mixed): (bool|int)
	 */
	#[Pure] public static function invert(callable $comparer): callable
	{
		return static function (mixed ...$args) use ($comparer) {
			$result = $comparer(...$args);

			if (is_bool($result)) {
				return !$result;
			}

			/** @psalm-suppress RedundantConditionGivenDocblockType */
			if (is_numeric($result)) {
				return -$result;
			}

			throw new InvalidArgumentException('Invalid comparer result: ' . get_debug_type($result));
		};
	}
}
