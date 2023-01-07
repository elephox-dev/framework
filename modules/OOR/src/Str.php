<?php
declare(strict_types=1);

namespace Elephox\OOR;

use JetBrains\PhpStorm\Pure;
use Stringable;

class Str implements Stringable
{
	#[Pure]
	public static function wrap(string|Stringable|self $string): self
	{
		if ($string instanceof self) {
			return $string;
		}

		return new self((string) $string);
	}

	public static function implode(string|Stringable|self|null $separator, string|Stringable|self $string, string|Stringable|self ...$concat): self
	{
		if ($separator instanceof self) {
			$separator = (string) $separator;
		}

		$values = Arr::wrap($concat);
		$values->unshift($string);

		return new self($values->map(static fn (string|self $str) => self::mapStrToString($str))->implode($separator));
	}

	private static function mapStrToString(string|self $string): string
	{
		if ($string instanceof self) {
			return $string->source;
		}

		return $string;
	}

	private static function mapStrsToStrings(iterable ...$values): iterable
	{
		/**
		 * @var mixed $key
		 * @var mixed $value
		 */
		foreach ($values as $key => $value) {
			if ($key instanceof Stringable && $value instanceof Stringable) {
				yield $key->__toString() => $value->__toString();
			} elseif ($key instanceof Stringable) {
				yield $key->__toString() => $value;
			} elseif ($value instanceof Stringable) {
				yield $key => $value->__toString();
			} else {
				yield $key => $value;
			}
		}
	}

	/**
	 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Support/Str.php#L361
	 *
	 * @return bool
	 */
	public static function is(string|Stringable|iterable $patterns, null|int|float|bool|string|Stringable $value): bool
	{
		$value = (string) $value;

		if (!is_iterable($patterns)) {
			$patterns = [$patterns];
		}

		foreach ($patterns as $pattern) {
			$pattern = (string) $pattern;

			// If the given value is an exact match we can of course return true right
			// from the beginning. Otherwise, we will translate asterisks and do an
			// actual pattern match against the two strings to see if they match.
			if ($pattern === $value) {
				return true;
			}

			$pattern = preg_quote($pattern, '#');

			// Asterisks are translated into zero-or-more regular expression wildcards
			// to make it convenient to check if the strings starts with the given
			// pattern such as "library/*", making any string check convenient.
			$pattern = str_replace('\*', '.*', $pattern);

			if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
				return true;
			}
		}

		return false;
	}

	#[Pure]
	public function __construct(
		private readonly string $source,
	) {
	}

	#[Pure]
	public function getSource(): string
	{
		return $this->source;
	}

	#[Pure]
	public function explode(string|Stringable $separator, int $limit = PHP_INT_MAX): Arr
	{
		return Arr::wrap(explode((string) $separator, $this->source, $limit));
	}

	public function sprintf(float|int|string|Stringable|self ...$values): self
	{
		/** @psalm-suppress InvalidArgument */
		return self::wrap(sprintf($this->source, ...self::mapStrsToStrings($values)));
	}

	#[Pure]
	public function __toString(): string
	{
		return $this->source;
	}

	#[Pure]
	public function concat(string|Stringable|self $value): self
	{
		return self::wrap($this->source . (string) $value);
	}

	public function startsWith(string|Stringable|self $value): bool
	{
		return str_starts_with($this->source, (string) $value);
	}

	public function contains_any(string ...$needles): bool
	{
		foreach ($needles as $needle) {
			if (str_contains($this->source, $needle)) {
				return true;
			}
		}

		return false;
	}
}
