<?php
declare(strict_types=1);

namespace Elephox\OOR;

use Exception;
use JetBrains\PhpStorm\Pure;

class Casing
{
	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "hello beautiful world"
	 */
	#[Pure]
	public static function toLower(string $string): string
	{
		return mb_strtolower($string, 'UTF-8');
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "HELLO BEAUTIFUL WORLD"
	 */
	#[Pure]
	public static function toUpper(string $string): string
	{
		return mb_strtoupper($string, 'UTF-8');
	}

	/**
	 * Example input: "Hello beautiful WoRld"<br>
	 * Example output: "Hello Beautiful World"
	 */
	#[Pure]
	public static function toTitle(string $string): string
	{
		return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
	}

	/**
	 * Example input: "Hello beautiful World", "-"<br>
	 * Example output: "Hello-beautiful-World"
	 */
	#[Pure]
	public static function replaceDelimiters(string $string, string $replacement, string $delimitersPattern = '/([\s\-_]+)/'): string
	{
		/** @var string */
		return preg_replace($delimitersPattern, $replacement, $string);
	}

	/**
	 * Example input: "HelloBeautifulWorld", "-"<br>
	 * Example output: "Hello-Beautiful-World"
	 */
	#[Pure]
	public static function splitWords(string $string, string $separator): string
	{
		$perimeter = match ($separator) {
			'/' => '#',
			default => '/'
		};

		/** @var string */
		return preg_replace($perimeter . '([a-z])([A-Z])' . $perimeter, '$1' . $separator . '$2', $string);
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "helloBeautifulWorld"
	 */
	#[Pure]
	public static function toCamel(string $string): string
	{
		return lcfirst(self::toPascal($string));
	}

	/**
	 * Example input: "Hello beautifulWorld"<br>
	 * Example output: "hello_beautiful_world"
	 */
	#[Pure]
	public static function toSnake(string $string): string
	{
		return self::toLower(self::replaceDelimiters(self::splitWords($string, '_'), '_'));
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "hello-beautiful-world"
	 */
	#[Pure]
	public static function toKebab(string $string): string
	{
		return self::toLower(self::toHttpHeader($string));
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "HELLO-BEAUTIFUL-WORLD"
	 */
	#[Pure]
	public static function toCobol(string $string): string
	{
		return self::toUpper(self::toHttpHeader($string));
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "HelloBeautifulWorld"
	 */
	#[Pure]
	public static function toHttpHeader(string $string): string
	{
		return self::replaceDelimiters(self::toTitle(self::splitWords(self::toLower($string), ' ')), '-');
	}

	/**
	 * Example input: "Hello beautiful WoRld"<br>
	 * Example output: "HelloBeautifulWorld"
	 */
	#[Pure]
	public static function toPascal(string $string): string
	{
		return ucfirst(self::replaceDelimiters(self::toTitle(self::splitWords($string, ' ')), ''));
	}

	/**
	 * Changes all characters randomly to lower or upper case using the current timestamp as a seed.
	 * You can also pass your own seed for reproducible results.
	 *
	 * Example input: "Hello beautiful World", 1<br>
	 * Example output: "heLLo beaUTiFul WoRLD"
	 */
	public static function random(string $string, ?int $seed = null): string
	{
		try {
			mt_srand($seed ?? time());

			for ($i = 0, $iMax = strlen($string); $i < $iMax; $i++) {
				$string[$i] = mt_rand(0, 1) === 0 ? self::toUpper($string[$i]) : self::toLower($string[$i]);
			}

			return $string;
		} catch (Exception) {
			return $string;
		}
	}
}
