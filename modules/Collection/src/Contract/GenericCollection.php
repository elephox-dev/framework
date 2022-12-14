<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Elephox\Collection\ArrayList;
use JetBrains\PhpStorm\ExpectedValues;
use JsonException;

use const JSON_FORCE_OBJECT;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_INVALID_UTF8_IGNORE;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_NUMERIC_CHECK;
use const JSON_PARTIAL_OUTPUT_ON_ERROR;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_LINE_TERMINATORS;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const JSON_THROW_ON_ERROR;

/**
 * @psalm-type NonNegativeInteger = int<0,max>
 *
 * @template TSource
 */
interface GenericCollection
{
	/**
	 * @param NonNegativeInteger $size
	 *
	 * @return GenericEnumerable<non-empty-list<TSource>>
	 */
	public function chunk(int $size): GenericEnumerable;

	/**
	 * @param TSource $value
	 * @param null|callable(TSource, TSource): bool $comparer
	 */
	public function contains(mixed $value, ?callable $comparer = null): bool;

	public function isEmpty(): bool;

	public function isNotEmpty(): bool;

	/**
	 * @param callable(TSource): void $callback
	 *
	 * @return void
	 */
	public function forEach(callable $callback): void;

	/**
	 * @return list<TSource>
	 */
	public function toList(): array;

	/**
	 * @return ArrayList<TSource>
	 */
	public function toArrayList(): ArrayList;

	/**
	 * @throws JsonException
	 */
	public function toJson(
		#[ExpectedValues(flags: [
			JSON_FORCE_OBJECT,
			JSON_HEX_QUOT,
			JSON_HEX_TAG,
			JSON_HEX_AMP,
			JSON_HEX_APOS,
			JSON_INVALID_UTF8_IGNORE,
			JSON_INVALID_UTF8_SUBSTITUTE,
			JSON_NUMERIC_CHECK,
			JSON_PARTIAL_OUTPUT_ON_ERROR,
			JSON_PRESERVE_ZERO_FRACTION,
			JSON_PRETTY_PRINT,
			JSON_UNESCAPED_LINE_TERMINATORS,
			JSON_UNESCAPED_SLASHES,
			JSON_UNESCAPED_UNICODE,
			JSON_THROW_ON_ERROR,
		])] int $flags = 0,
		int $depth = 512,
	): string;
}
