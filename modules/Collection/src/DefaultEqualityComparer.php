<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\Comparable;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Stringable;

final class DefaultEqualityComparer
{
	#[Pure]
	public static function equals(mixed $a, mixed $b): bool
	{
		if (is_object($a) || is_object($b)) {
			if ($a instanceof Comparable && is_object($b)) {
				return $a->compareTo($b) === 0;
			}

			if ($b instanceof Comparable && is_object($a)) {
				return $b->compareTo($a) === 0;
			}

			return false;
		}

		/** @noinspection TypeUnsafeComparisonInspection */
		return $a == $b;
	}

	#[Pure]
	public static function same(mixed $a, mixed $b): bool
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

	#[Pure]
	public static function compare(mixed $a, mixed $b): int
	{
		if (is_object($a) || is_object($b)) {
			if ($a instanceof Comparable && is_object($b)) {
				return $a->compareTo($b);
			}

			if ($b instanceof Comparable && is_object($a)) {
				return $b->compareTo($a);
			}

			if (is_object($b) && $a instanceof ($b::class)) {
				return $a <=> $b;
			}

			throw new InvalidArgumentException('Cannot compare random objects');
		}

		return $a <=> $b;
	}

	/**
	 * @template TCallable as
	 *
	 * @param callable(...mixed): (bool|numeric) $comparer
	 *
	 * @return callable(...mixed): (bool|numeric)
	 */
	#[Pure]
	public static function invert(callable $comparer): callable
	{
		return static function (mixed ...$args) use ($comparer) {
			/** @var mixed $result */
			$result = $comparer(...$args);

			if (is_bool($result)) {
				return !$result;
			}

			if (is_numeric($result)) {
				return -$result;
			}

			throw new InvalidArgumentException('Invalid comparer result: ' . get_debug_type($result));
		};
	}
}
