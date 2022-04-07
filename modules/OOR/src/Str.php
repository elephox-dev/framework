<?php
declare(strict_types=1);

namespace Elephox\OOR;

use JetBrains\PhpStorm\Pure;
use Stringable;

class Str implements Stringable
{
	#[Pure]
	public static function wrap(string|self $string): self
	{
		if ($string instanceof self) {
			return $string;
		}

		return new self($string);
	}

	public static function implode(string|self|null $separator, string|self $string, string|self ...$concat): self
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
			if ($key instanceof self && $value instanceof self) {
				yield $key->source => $value->source;
			} elseif ($key instanceof self) {
				yield $key->source => $value;
			} elseif ($value instanceof self) {
				yield $key => $value->source;
			} else {
				yield $key => $value;
			}
		}
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
	public function explode(string $separator, int $limit = PHP_INT_MAX): Arr
	{
		return Arr::wrap(explode($separator, $this->source, $limit));
	}

	public function sprintf(float|int|string|self ...$values): self
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
	public function concat(string|self $value): self
	{
		return self::wrap($this->source . $value);
	}

	public function startsWith(string|self $value): bool
	{
		return str_starts_with($this->source, (string) $value);
	}
}
