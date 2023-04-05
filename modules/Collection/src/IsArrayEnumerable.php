<?php
declare(strict_types=1);

namespace Elephox\Collection;

/**
 * @template TKey
 * @template TValue
 */
trait IsArrayEnumerable
{
	/**
	 * @var array<TKey, TValue>
	 */
	protected array $items;

	//	/**
	//	 * @template TDefault
	//	 *
	//	 * @param TDefault $defaultValue
	//	 * @param null|callable(TValue, TKey): bool $predicate
	//	 *
	//	 * @return TDefault|TValue
	//	 */
	//	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed
	//	{
	//		// FIXME: this seems to be worse than the generic implementation in IsEnumerable (according to phpbench). This can be improved.
	//		if ($predicate === null) {
	//			if (empty($this->items)) {
	//				return $defaultValue;
	//			}
	//
	//			return reset($this->items);
	//		}
	//
	//		$result = array_filter($this->items, $predicate);
	//		if (empty($result)) {
	//			return $defaultValue;
	//		}
	//
	//		return reset($result);
	//	}

	/**
	 * @param TValue $value
	 * @param null|callable(TValue, TValue): bool $comparer
	 */
	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		foreach ($this->items as $v) {
			if ($comparer($v, $value)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param TKey $key
	 * @param null|callable(TKey, TKey): bool $comparer
	 */
	public function containsKey(mixed $key, ?callable $comparer = null): bool
	{
		if ($comparer === null) {
			return array_key_exists($key, $this->items);
		}

		foreach ($this->items as $k => $v) {
			if ($comparer($key, $k)) {
				return true;
			}
		}

		return false;
	}
}
