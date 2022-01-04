<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;

final class DefaultEqualityComparer
{
	#[Pure] public static function equals(mixed $a, mixed $b): bool
	{
		if ($a instanceof Comparable && is_object($b)) {
			return self::comparableEquals($a, $b);
		}

		if ($b instanceof Comparable && is_object($a)) {
			return self::comparableEquals($b, $a);
		}

		/** @noinspection TypeUnsafeComparisonInspection */
		return $a == $b;
	}

	#[Pure] public static function same(mixed $a, mixed $b): bool
	{
		if (is_object($a) && is_object($b)) {
			if ($a instanceof Comparable) {
				return self::comparableEquals($a, $b);
			}

			if ($b instanceof Comparable) {
				return self::comparableEquals($b, $a);
			}

			return self::sameObject($a, $b);
		}

		if (!is_object($a) || !is_object($b)) {
			return false;
		}

		return $a === $b;
	}

	#[Pure] public static function sameObject(object $a, object $b): bool
	{
		return spl_object_hash($a) === spl_object_hash($b);
	}

	#[Pure] public static function comparableEquals(Comparable $a, object $b): bool
	{
		return $a->compareTo($b) === 0;
	}

	#[Pure] public static function compare(mixed $a, mixed $b): int
	{
		if (is_object($a) && is_object($b)) {
			if ($a instanceof Comparable) {
				return $a->compareTo($b);
			}

			if ($b instanceof Comparable) {
				return $b->compareTo($a);
			}
		}

		return $a <=> $b;
	}

	/**
	 * @template TCallable as callable(mixed, mixed): (bool|int)
	 *
	 * @param TCallable $comparer
	 * @return callable(mixed, mixed): (bool|int)
	 */
	#[Pure] public static function invert(callable $comparer): callable
	{
		return static function (mixed $a, mixed $b) use ($comparer) {
			$result = $comparer($a, $b);

			if (is_bool($result)) {
				return !$result;
			}

			return -$result;
		};
	}
}
