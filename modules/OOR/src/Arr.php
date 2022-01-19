<?php
declare(strict_types=1);

namespace Elephox\OOR;

use ArrayAccess;
use ArrayIterator;
use Elephox\Collection\Enumerable;
use Elephox\Collection\KeyedEnumerable;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

/**
 * @psalm-suppress MixedArrayOffset
 * @psalm-suppress MixedTypeCoercion
 * @psalm-suppress MixedArgumentTypeCoercion
 */
final class Arr implements ArrayAccess, IteratorAggregate
{
	#[Pure]
	public static function wrap(mixed ...$values): self
	{
		if (count($values) === 1 && is_array($values[0])) {
			return new self($values[0]);
		}

		return new self($values);
	}

	#[Pure]
	public static function combine(self|array $keys, self|array $values): self
	{
		return new self(array_combine(self::mapToArray($keys), self::mapToArray($values)));
	}

	#[Pure]
	public static function range(string|int|float $start, string|int|float $end, int|float $step): self
	{
		return new self(range($start, $end, $step));
	}

	/**
	 * @param Arr|array $array
	 *
	 * @return array
	 */
	#[Pure]
	private static function mapToArray(self|array $array): array
	{
		if ($array instanceof self) {
			return $array->source;
		}

		return $array;
	}

	/**
	 * @param Arr|array ...$arrays
	 *
	 * @return iterable<int, array>
	 */
	private static function mapAllToArray(self|array ...$arrays): iterable
	{
		foreach ($arrays as $array) {
			yield self::mapToArray($array);
		}
	}

	#[Pure]
	public function __construct(
		private array $source,
	)
	{
	}

	/**
	 * @throws \Exception
	 */
	public function asEnumerable(): Enumerable
	{
		return new Enumerable($this->getIterator());
	}

	/**
	 * @throws \Exception
	 */
	public function asKeyedEnumerable(): KeyedEnumerable
	{
		return new KeyedEnumerable($this->getIterator());
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->source);
	}

	#[Pure]
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->source[$offset]);
	}

	#[Pure]
	public function offsetGet(mixed $offset): mixed
	{
		/** @psalm-suppress MixedReturnStatement */
		return $this->source[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->source[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->source[$offset]);
	}

	#[Pure]
	public function changeKeyCase(#[ExpectedValues([CASE_LOWER, CASE_UPPER])] int $case = CASE_LOWER): self
	{
		return new self(array_change_key_case($this->source, $case));
	}

	#[Pure]
	public function chunk(int $length, bool $preserve_keys = false): self
	{
		return new self(array_chunk($this->source, $length, $preserve_keys));
	}

	#[Pure]
	public function column(int|string|null $column_key, int|string|null $index_key = null): self
	{
		return new self(array_column($this->source, $column_key, $index_key));
	}

	#[Pure]
	public function countValues(): self
	{
		return new self(array_count_values($this->source));
	}

	/**
	 * @psalm-suppress TooManyArguments
	 */
	public function diff(self|array $array, Diff $type = Diff::Normal, ?callable $key_compare_func = null, self|array ...$rest): self
	{
		$firstArray = self::mapToArray($array);
		$restArrays = self::mapAllToArray(...$rest);

		$result = match ($type) {
			Diff::Normal => $key_compare_func !== null ? throw new InvalidArgumentException("Cannot use a key compare function for normal array diff.") : array_diff($this->source, $firstArray, ...$restArrays),
			Diff::Assoc => $key_compare_func !== null ? array_diff_uassoc($this->source, $firstArray, $key_compare_func, ...$restArrays) : array_diff_assoc($this->source, $firstArray, ...$restArrays),
			Diff::Key => $key_compare_func !== null ? array_diff_ukey($this->source, $firstArray, $key_compare_func, ...$restArrays) : array_diff_key($this->source, $firstArray, ...$restArrays),
		};

		return new self($result);
	}

	#[Pure]
	public function fillKeys(mixed $value): self
	{
		return new self(array_fill_keys($this->source, $value));
	}

	#[Pure]
	public function fill(int $start_index, int $count, mixed $value): self
	{
		return new self(array_fill($start_index, $count, $value));
	}

	public function filter(?callable $callback = null, Filter $mode = Filter::Value): self
	{
		$intMode = match ($mode) {
			Filter::Value => 0,
			Filter::Key => ARRAY_FILTER_USE_KEY,
			Filter::Both => ARRAY_FILTER_USE_BOTH,
		};

		if ($callback === null) {
			return new self(array_filter($this->source, mode: $intMode));
		}

		return new self(array_filter($this->source, $callback, $intMode));
	}

	#[Pure]
	public function flip(): self
	{
		return new self(array_flip($this->source));
	}

	/**
	 * @psalm-suppress TooManyArguments
	 */
	public function intersect(Intersect $mode, array|self $array, ?callable $callback = null, array|self ...$rest): self
	{
		$firstArray = self::mapToArray($array);
		$restArrays = self::mapAllToArray(...$rest);

		$intMode = match ($mode) {
			Intersect::Assoc => $callback !== null ? array_intersect_uassoc($this->source, $firstArray, $callback, ...$restArrays) : array_intersect_assoc($this->source, $firstArray, ...$restArrays),
			Intersect::Key => $callback !== null ? array_intersect_ukey($this->source, $firstArray, $callback, ...$restArrays) : array_intersect_key($this->source, $firstArray, ...$restArrays),
		};

		return new self($intMode);
	}

	#[Pure]
	public function isList(): bool
	{
		return array_is_list($this->source);
	}

	#[Pure]
	public function keyExists(string|int $key): bool
	{
		return array_key_exists($key, $this->source);
	}

	#[Pure]
	public function keyFirst(): string|int|null
	{
		return array_key_first($this->source);
	}

	#[Pure]
	public function keyLast(): string|int|null
	{
		return array_key_last($this->source);
	}

	#[Pure]
	public function keys(): self
	{
		return new self(array_keys($this->source));
	}

	#[Pure]
	public function keysSearch(mixed $value, bool $strict = false): self
	{
		return new self(array_keys($this->source, $value, $strict));
	}

	public function map(?callable $callback, array|self ...$arrays): self
	{
		return new self(array_map($callback, $this->source, ...self::mapAllToArray(...$arrays)));
	}

	public function mergeRecursive(array|self ...$arrays): self
	{
		return new self(array_merge_recursive($this->source, ...self::mapAllToArray(...$arrays)));
	}

	public function merge(array|self ...$arrays): self
	{
		return new self(array_merge($this->source, ...self::mapAllToArray(...$arrays)));
	}

	#[Pure]
	public function pad(int $length, mixed $value): self
	{
		return new self(array_pad($this->source, $length, $value));
	}

	public function pop(): mixed
	{
		return array_pop($this->source);
	}

	#[Pure]
	public function product(): int|float
	{
		return array_product($this->source);
	}

	public function push(mixed ...$values): int
	{
		return array_push($this->source, ...$values);
	}

	public function rand(int $num = 1): int|string|self
	{
		if (empty($this->source)) {
			throw new InvalidArgumentException("Cannot get a random value from an empty array.");
		}

		$result = array_rand($this->source, $num);
		if (is_array($result)) {
			return new self($result);
		}

		return $result;
	}

	public function reduce(callable $callback, mixed $initial = null): mixed
	{
		return array_reduce($this->source, $callback, $initial);
	}

	public function replaceRecursive(array|self ...$replacements): self
	{
		return new self(array_replace_recursive($this->source, ...self::mapAllToArray(...$replacements)));
	}

	public function replace(array|self ...$replacements): self
	{
		return new self(array_replace($this->source, ...self::mapAllToArray(...$replacements)));
	}

	#[Pure]
	public function reverse(bool $preserve_keys = false): self
	{
		return new self(array_reverse($this->source, $preserve_keys));
	}

	#[Pure]
	public function search(mixed $needle, bool $strict = false): int|string|false
	{
		return array_search($needle, $this->source, $strict);
	}

	public function shift(): mixed
	{
		return array_shift($this->source);
	}

	#[Pure]
	public function slice(int $offset, ?int $length = null, bool $preserve_keys = false): self
	{
		return new self(array_slice($this->source, $offset, $length, $preserve_keys));
	}

	public function splice(int $offset, ?int $length = null, mixed $replacement = []): self
	{
		if ($length === null) {
			return new self(array_splice($this->source, $offset, replacement: (array)$replacement));
		}

		return new self(array_splice($this->source, $offset, $length, (array)$replacement));
	}

	#[Pure]
	public function sum(): int|float
	{
		return array_sum($this->source);
	}

	#[Pure]
	public function unique(#[ExpectedValues(flags: [SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING])] int $flags): self
	{
		return new self(array_unique($this->source, $flags));
	}

	public function unshift(mixed ...$values): int
	{
		return array_unshift($this->source, ...$values);
	}

	#[Pure]
	public function values(): self
	{
		return new self(array_values($this->source));
	}

	public function walkRecursive(callable $callback, mixed $arg = null): bool
	{
		return array_walk_recursive($this->source, $callback, $arg);
	}

	public function walk(callable $callback, mixed $arg = null): bool
	{
		return array_walk($this->source, $callback, $arg);
	}

	public function arsort(#[ExpectedValues(flags: [SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE])] int $flags = SORT_REGULAR): bool
	{
		return arsort($this->source, $flags);
	}

	public function asort(#[ExpectedValues(flags: [SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE])] int $flags = SORT_REGULAR): bool
	{
		return asort($this->source, $flags);
	}

	public function compact(array|self|string $var_name, array|self|string ...$var_names): self
	{
		if ($var_name instanceof self) {
			$var_name = $var_name->source;
		}

		$mapper = static function () use ($var_names): Generator {
			foreach ($var_names as $name) {
				if ($name instanceof self) {
					yield $name->source;
				}

				yield $name;
			}
		};

		return new self(compact($this->source, $var_name, ...$mapper()));
	}

	#[Pure]
	public function count(): int
	{
		return count($this->source);
	}

	#[Pure]
	public function current(): mixed
	{
		return current($this->source);
	}

	public function end(): mixed
	{
		return end($this->source);
	}

	#[Pure]
	public function contains(mixed $needle, bool $strict = false): bool
	{
		return in_array($needle, $this->source, $strict);
	}

	#[Pure]
	public function key(): int|string|null
	{
		return key($this->source);
	}

	public function krsort(#[ExpectedValues(flags: [SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE])] int $flags = SORT_REGULAR): bool
	{
		return krsort($this->source, $flags);
	}

	public function ksort(#[ExpectedValues(flags: [SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE])] int $flags = SORT_REGULAR): bool
	{
		return ksort($this->source, $flags);
	}

	public function natcasesort(): bool
	{
		return natcasesort($this->source);
	}

	public function natsort(): bool
	{
		return natsort($this->source);
	}

	public function next(): mixed
	{
		return next($this->source);
	}

	public function prev(): mixed
	{
		return prev($this->source);
	}

	public function reset(): mixed
	{
		return reset($this->source);
	}

	public function rsort(#[ExpectedValues(flags: [SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE])] int $flags = SORT_REGULAR): bool
	{
		return rsort($this->source, $flags);
	}

	public function shuffle(): bool
	{
		return shuffle($this->source);
	}

	public function sort(#[ExpectedValues(flags: [SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE])] int $flags = SORT_REGULAR): bool
	{
		return sort($this->source, $flags);
	}

	public function uasort(callable $callback): bool
	{
		return uasort($this->source, $callback);
	}

	public function uksort(callable $callback): bool
	{
		return uksort($this->source, $callback);
	}

	public function usort(callable $callback): bool
	{
		return usort($this->source, $callback);
	}

	public function implode(?string $separator = null): string
	{
		if ($separator === null) {
			return implode($this->source);
		}

		return implode($separator, $this->source);
	}
}
